<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Rental;
use App\Models\AuditLog;
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
 
    private function getPeriod(string $period, Request $request): array
    {
        return match($period) {
            'today'  => [now()->toDateString(), now()->toDateString()],
            'week'   => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'year'   => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'custom' => [$request->start_date ?? now()->startOfMonth()->toDateString(),
                         $request->end_date   ?? now()->toDateString()],
            default  => [now()->startOfMonth()->toDateString(), now()->toDateString()], // month
        };
    }
}