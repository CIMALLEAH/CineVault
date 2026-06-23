@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')
 
@section('content')
{{-- Period filter --}}
<form method="GET" style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; align-items:center;">
    @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $key=>$label)
        <a href="{{ request()->fullUrlWithQuery(['period'=>$key]) }}"
           class="btn {{ $period===$key ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            {{ $label }}
        </a>
    @endforeach
    <span style="color:var(--text3); font-size:12px; margin-left:4px;">
        {{ \Carbon\Carbon::parse($startDate)->format('M d') }} – {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
    </span>
</form>
 
{{-- Summary stats --}}
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px;">
    <div class="stat-card" style="--accent-color:var(--gold);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">Total Revenue</div>
        <div style="font-size:26px; font-weight:700; color:var(--gold);">₱{{ number_format($totalRevenue, 0) }}</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--blue);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">Total Rentals</div>
        <div style="font-size:26px; font-weight:700;">{{ $totalRentals }}</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--green);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">Avg per Rental</div>
        <div style="font-size:26px; font-weight:700;">₱{{ $totalRentals > 0 ? number_format($totalRevenue / $totalRentals, 0) : 0 }}</div>
    </div>
</div>
 
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
    {{-- Monthly Chart --}}
    <div class="card">
        <div class="card-body">
            <div style="font-size:13px; font-weight:600; margin-bottom:14px; color:var(--text2);">Monthly Revenue (Last 6 Months)</div>
            <canvas id="monthlyChart" height="140"></canvas>
        </div>
    </div>
 
    {{-- Genre Revenue --}}
    <div class="card">
        <div class="card-body">
            <div style="font-size:13px; font-weight:600; margin-bottom:14px; color:var(--text2);">Revenue by Genre</div>
            @php $maxRev = $revenueByGenre->max('revenue') ?: 1; @endphp
            @foreach($revenueByGenre as $g)
                <div style="margin-bottom:12px;">
                    <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                        <span>{{ $g->genre }}</span>
                        <span style="color:var(--gold); font-weight:600;">₱{{ number_format($g->revenue) }}</span>
                    </div>
                    <div style="height:5px; background:var(--bg4); border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:{{ round($g->revenue / $maxRev * 100) }}%; background:linear-gradient(90deg,var(--gold),var(--gold2)); border-radius:3px;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
 
{{-- Payment Methods --}}
<div style="display:grid; grid-template-columns:1fr 2fr; gap:16px; margin-bottom:16px;">
    <div class="card">
        <div class="card-body">
            <div style="font-size:13px; font-weight:600; margin-bottom:14px; color:var(--text2);">Payment Methods</div>
            @foreach($paymentBreakdown as $p)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border);">
                    <div>
                        <span class="badge {{ $p->payment_method==='gcash' ? 'badge-blue' : ($p->payment_method==='card' ? 'badge-purple' : 'badge-green') }}">
                            {{ strtoupper($p->payment_method) }}
                        </span>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:13px; font-weight:600;">{{ $p->count }}x</div>
                        <div style="font-size:11px; color:var(--gold);">₱{{ number_format($p->total) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
 
    {{-- Top Movies --}}
    <div class="card" style="overflow-x:auto;">
        <div class="card-body" style="padding-bottom:0;">
            <div style="font-size:13px; font-weight:600; margin-bottom:14px; color:var(--text2);">Top Rented Movies</div>
        </div>
        <table class="data-table">
            <thead><tr><th>#</th><th>Movie</th><th>Genre</th><th>Times Rented</th><th>Revenue</th></tr></thead>
            <tbody>
                @foreach($topMovies as $i => $m)
                    <tr>
                        <td style="color:var(--text3); font-weight:700;">{{ $i+1 }}</td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:18px;"><i class="{{ $m->poster_icon }}"></i></span>
                                <span style="font-weight:500;">{{ $m->title }}</span>
                            </div>
                        </td>
                        <td><span class="badge badge-gold">{{ $m->genre }}</span></td>
                        <td style="font-weight:600;">{{ $m->rent_count }}x</td>
                        <td style="font-weight:700; color:var(--gold);">₱{{ number_format($m->revenue) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
 
{{-- Audit Logs --}}
<div class="card">
    <div class="card-body" style="padding-bottom:0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div style="font-size:13px; font-weight:600; color:var(--text2);">Recent Audit Logs</div>
            <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary btn-sm">View All</a>
        </div>
    </div>
    <table class="data-table">
        <thead><tr><th>Action</th><th>Description</th><th>User</th><th>IP</th><th>Time</th></tr></thead>
        <tbody>
            @foreach($auditLogs as $log)
                <tr>
                    <td><span class="badge badge-gold" style="font-size:10px;">{{ $log->action }}</span></td>
                    <td style="font-size:12px;">{{ $log->description }}</td>
                    <td style="font-size:12px;">{{ $log->user?->name ?? 'System' }}</td>
                    <td style="font-size:11px; color:var(--text3);">{{ $log->ip_address }}</td>
                    <td style="font-size:11px; color:var(--text3);">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination">{{ $auditLogs->links() }}</div>
</div>
@endsection
 
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const monthly = @json($monthlyRevenue);
const labels  = monthly.map(r => {
    const d = new Date(r.year, r.month - 1);
    return d.toLocaleString('default', { month: 'short', year: '2-digit' });
});
const data = monthly.map(r => parseFloat(r.total));
 
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Revenue',
            data,
            backgroundColor: 'rgba(200,160,74,0.3)',
            borderColor: '#c8a04a',
            borderWidth: 2,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: '#2a2a42' }, ticks: { color: '#787068' } },
            y: { grid: { color: '#2a2a42' }, ticks: { color: '#787068', callback: v => '₱' + v.toLocaleString() } }
        }
    }
});
</script>
@endsection