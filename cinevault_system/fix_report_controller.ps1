$content = @'
<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Rental;
use App\Models\AuditLog;
use App\Models\ExportHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        [$startDate, $endDate] = $this->getPeriod($period, $request);
 
        // Revenue totals
        $totalRevenue = Rental::whereBetween('rental_date', [$startDate, $endDate])
                               ->sum('total_amount');
 
        $totalRentals = Rental::whereBetween('rental_date', [$startDate, $endDate])->count();
 
        // Revenue by genre
        $revenueByGenre = Rental::join('movies', 'rentals.movie_id', '=', 'movies.id')
            ->whereBetween('rentals.rental_date', [$startDate, $endDate])
            ->select('movies.genre', DB::raw('SUM(rentals.total_amount) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('movies.genre')
            ->orderByDesc('revenue')
            ->get();
 
        // Monthly revenue (last 6 months)
        $monthlyRevenue = Rental::select(
                DB::raw('YEAR(rental_date) as year'),
                DB::raw('MONTH(rental_date) as month'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as rentals')
            )
            ->where('rental_date', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get();
 
        // Top rented movies
        $topMovies = Rental::join('movies', 'rentals.movie_id', '=', 'movies.id')
            ->whereBetween('rentals.rental_date', [$startDate, $endDate])
            ->select(
                'movies.id', 'movies.title', 'movies.genre', 'movies.poster_icon',
                DB::raw('COUNT(rentals.id) as rent_count'),
                DB::raw('SUM(rentals.total_amount) as revenue')
            )
            ->groupBy('movies.id', 'movies.title', 'movies.genre', 'movies.poster_icon')
            ->orderByDesc('rent_count')
            ->take(10)
            ->get();
 
        // Payment method breakdown
        $paymentBreakdown = Rental::whereBetween('rental_date', [$startDate, $endDate])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();
 
        // Audit logs
        $auditLogs = AuditLog::with('user')
            ->latest()
            ->paginate(20);
 
        return view('admin.reports.index', compact(
            'totalRevenue', 'totalRentals', 'revenueByGenre',
            'monthlyRevenue', 'topMovies', 'paymentBreakdown',
            'auditLogs', 'period', 'startDate', 'endDate'
        ));
    }
 
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');
 
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
 
        $logs = $query->latest()->paginate(30)->withQueryString();
 
        return view('admin.reports.audit', compact('logs'));
    }
 
    public function exportAuditLogs(Request $request)
    {
        $query = AuditLog::with('user');
 
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
 
        $logs = $query->latest()->get();
 
        $filename = 'cinevault-audit-logs-'.now()->format('YmdHis').'.xlsx';
        $relativePath = 'exports/audit-logs/'.$filename;
        $storagePath = storage_path('app/'.$relativePath);
 
        if (!is_dir(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0755, true);
        }
 
        $this->writeXlsx($logs, $storagePath);
 
        ExportHistory::create([
            'user_id'  => auth()->id(),
            'filename' => $filename,
            'path'     => $relativePath,
            'filters'  => $request->only(['action', 'user_id']),
        ]);
 
        return response()->download($storagePath, $filename);
    }
 
    private function writeXlsx(Collection $logs, string $path): void
    {
        $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>\n<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">\n<sheetData>';
        $xmlFooter = '</sheetData></worksheet>';
 
        $rows = [];
        $headers = ['ID','Date','Action','Description','User','IP Address','Model Type','Model ID'];
        $rows[] = $this->xmlRow($headers);
 
        foreach ($logs as $log) {
            $rows[] = $this->xmlRow([
                $log->id,
                $log->created_at?->format('Y-m-d H:i:s'),
                $log->action,
                $log->description,
                $log->user?->name ?? 'System',
                $log->ip_address,
                $log->model_type,
                $log->model_id,
            ]);
        }
 
        $sheetXml = $xmlHeader . implode('', $rows) . $xmlFooter;
        $workbookXml = $this->workbookXml();
        $relsXml = $this->relsXml();
        $contentTypesXml = $this->contentTypesXml();
        $stylesXml = $this->stylesXml();
 
        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create XLSX file');
        }
 
        $zip->addFromString('_rels/.rels', $relsXml);
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->addFromString('xl/styles.xml', $stylesXml);
        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->close();
    }
 
    private function xmlRow(array $cells): string
    {
        $rowXml = '<row>';
 
        foreach ($cells as $cell) {
            $value = htmlspecialchars((string) $cell, ENT_QUOTES | ENT_XML1);
            $rowXml .= "<c t=\"inlineStr\"><is><t>{$value}</t></is></c>";
        }
 
        return $rowXml . '</row>';
    }
 
    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>\n<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">\n<sheets><sheet name="Audit Logs" sheetId="1" r:id="rId1"/></sheets>\n</workbook>';
    }
 
    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>\n<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">\n<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>\n</Relationships>';
    }
 
    private function relsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>\n<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">\n<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>\n</Relationships>';
    }
 
    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>\n<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">\n<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>\n<Default Extension="xml" ContentType="application/xml"/>\n<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>\n<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>\n<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>\n</Types>';
    }
 
    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>\n<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">\n<fonts count="1"><font><sz val="11"/><color rgb="FF000000"/><name val="Calibri"/></font></fonts>\n<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>\n<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>\n<CellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></CellStyleXfs>\n<CellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></CellXfs>\n</styleSheet>';
    }
 
    private function getPeriod(string $period, Request $request): array
    {
        return match($period) {
            'today'  => [now()->toDateString(), now()->toDateString()],
            'week'   => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'year'   => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'custom' => [$request->start_date ?? now()->startOfMonth()->toDateString(),
                         $request->end_date   ?? now()->toDateString()],
            default  => [now()->startOfMonth()->toDateString(), now()->toDateString()],
        };
    }
}
'@;
Set-Content -Path 'app\Http\Controllers\Admin\ReportController.php' -Value $content -Encoding utf8;
Write-Host 'ReportController rewritten'