@extends('layouts.app')
@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Dashboard')

@section('topbar-actions')
    <a href="{{ route('staff.rentals.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Rental
    </a>
@endsection

@section('content')
{{-- Stats --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px;">
    <div class="stat-card" style="--accent-color:var(--green);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">Available Movies</div>
        <div style="font-size:28px; font-weight:700; letter-spacing:-1px;">{{ $stats['available_movies'] }}</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--red);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">Active Rentals</div>
        <div style="font-size:28px; font-weight:700; letter-spacing:-1px;">{{ $stats['active_rentals'] }}</div>
        <div style="display:flex; align-items:center; gap:5px; margin-top:4px; font-size:11px; color:var(--red);"><span class="pulse"></span> Live</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--gold);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">My Pending Requests</div>
        <div style="font-size:28px; font-weight:700; letter-spacing:-1px;">{{ $stats['my_pending_requests'] }}</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--blue);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">Processed Today</div>
        <div style="font-size:28px; font-weight:700; letter-spacing:-1px;">{{ $stats['processed_today'] }}</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
    {{-- Recent Rentals --}}
    <div class="card">
        <div class="card-body">
            <div style="font-size:13px; font-weight:600; margin-bottom:14px; color:var(--text2);">My Recent Rentals</div>
            @forelse($recentRentals as $r)
                <div style="display:flex; align-items:center; gap:10px; padding:9px 0; border-bottom:1px solid var(--border);">
                    <span style="font-size:22px;">{{ $r->movie->poster_icon }}</span>
                    <div style="flex:1;">
                        <div style="font-size:12px; font-weight:500;">{{ $r->movie->title }}</div>
                        <div style="font-size:11px; color:var(--text3);">{{ $r->customer_name }} · {{ $r->rental_date->format('M d') }}</div>
                    </div>
                    <span class="badge {{ $r->status==='active' ? 'badge-red' : 'badge-green' }}">{{ ucfirst($r->status) }}</span>
                </div>
            @empty
                <div style="font-size:13px; color:var(--text3);">No recent rentals processed.</div>
            @endforelse
            <a href="{{ route('staff.rentals.index') }}" class="btn btn-secondary btn-sm" style="width:100%; justify-content:center; margin-top:12px;">View All</a>
        </div>
    </div>

    {{-- My Requests --}}
    <div class="card">
        <div class="card-body">
            <div style="font-size:13px; font-weight:600; margin-bottom:14px; color:var(--text2);">My Approval Requests</div>
            @forelse($myApprovals as $a)
                <div style="display:flex; align-items:center; gap:10px; padding:9px; background:var(--bg3); border:1px solid var(--border); border-radius:6px; margin-bottom:7px;">
                    <div style="flex:1;">
                        <div style="font-size:12px; font-weight:500;">{{ $a->getTypeLabel() }}: {{ $a->payload['title'] ?? $a->movie?->title ?? '—' }}</div>
                        <div style="font-size:11px; color:var(--text3);">{{ $a->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="badge {{ $a->status==='pending' ? 'badge-gold' : ($a->status==='approved' ? 'badge-green' : 'badge-red') }}">{{ ucfirst($a->status) }}</span>
                </div>
            @empty
                <div style="font-size:13px; color:var(--text3);">No requests submitted.</div>
            @endforelse
            <a href="{{ route('staff.approvals.index') }}" class="btn btn-secondary btn-sm" style="width:100%; justify-content:center; margin-top:12px;">View All</a>
        </div>
    </div>
</div>

{{-- Active Rentals --}}
<div class="card">
    <div class="card-body" style="padding-bottom:0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div style="font-size:13px; font-weight:600; color:var(--text2); display:flex; align-items:center; gap:8px;">
                Active Rentals <span class="pulse"></span>
            </div>
            <a href="{{ route('staff.rentals.index') }}" class="btn btn-secondary btn-sm">Manage</a>
        </div>
    </div>
    <table class="data-table">
        <thead><tr><th>Movie</th><th>Customer</th><th>Due Date</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
            @forelse($activeRentals as $r)
                <tr>
                    <td><div style="display:flex; align-items:center; gap:8px;"><span style="font-size:18px;">{{ $r->movie->poster_icon }}</span>{{ $r->movie->title }}</div></td>
                    <td>{{ $r->customer_name }}</td>
                    <td><span style="color:{{ $r->isOverdue() ? 'var(--red)' : 'var(--text)' }};">{{ $r->due_date->format('M d, Y') }}</span></td>
                    <td style="color:var(--gold); font-weight:600;">₱{{ number_format($r->total_amount) }}</td>
                    <td><span class="badge {{ $r->isOverdue() ? 'badge-red' : 'badge-red' }}"><span class="pulse" style="width:5px;height:5px;margin-right:3px;"></span>Active</span></td>
                    <td>
                        <form method="POST" action="{{ route('staff.rentals.return', $r) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm">Return</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center; padding:24px; color:var(--text3);">No active rentals.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
