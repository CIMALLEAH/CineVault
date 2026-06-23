@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')
 
@section('topbar-actions')
    <a href="{{ route('admin.rentals.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Rental
    </a>
@endsection
 
@section('content')
{{-- Stats Grid --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px;">
    <div class="stat-card" style="--accent-color:var(--gold);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">Total Movies</div>
        <div style="font-size:28px; font-weight:700; letter-spacing:-1px;">{{ $stats['total_movies'] }}</div>
        <div style="font-size:11px; color:var(--text3); margin-top:4px;">In catalog</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--red);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">Active Rentals</div>
        <div style="font-size:28px; font-weight:700; letter-spacing:-1px;">{{ $stats['active_rentals'] }}</div>
        <div style="display:flex; align-items:center; gap:5px; margin-top:4px; font-size:11px; color:var(--red);">
            <span class="pulse"></span> Live tracking
        </div>
    </div>
    <div class="stat-card" style="--accent-color:var(--blue);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">Revenue This Month</div>
        <div style="font-size:28px; font-weight:700; letter-spacing:-1px;">₱{{ number_format($stats['revenue_month'], 0) }}</div>
        <div style="font-size:11px; color:var(--text3); margin-top:4px;">{{ now()->format('F Y') }}</div>
    </div>
    <div class="stat-card" style="--accent-color:var(--purple);">
        <div style="font-size:11px; color:var(--text3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px;">Registered Users</div>
        <div style="font-size:28px; font-weight:700; letter-spacing:-1px;">{{ $stats['total_users'] }}</div>
        @if($stats['pending_approvals'] > 0)
            <div style="font-size:11px; color:var(--gold); margin-top:4px;">{{ $stats['pending_approvals'] }} pending approval(s)</div>
        @endif
    </div>
</div>
@endsection