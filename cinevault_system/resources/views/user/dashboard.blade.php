@extends('layouts.app')
@section('title', 'My Dashboard')
@section('page-title', 'Welcome, ' . auth()->user()->name)

@section('topbar-actions')
    <a href="{{ route('user.movies.index') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Browse Movies
    </a>
@endsection

@section('content')
{{-- Active Rentals --}}
@if($activeRentals->count())
    <div style="margin-bottom:22px;">
        <div style="font-size:14px; font-weight:600; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
            <span class="pulse"></span> My Active Rentals
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px;">
            @foreach($activeRentals as $rental)
                <div class="card" style="overflow:hidden; border-color:{{ $rental->isOverdue() ? 'rgba(224,82,82,.4)' : 'var(--border)' }};">
                    <div style="display:flex; align-items:center; gap:14px; padding:16px;">
                        <span style="font-size:40px;"><i class="{{ $rental->movie->poster_icon }}"></i></span>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600; font-size:14px; margin-bottom:3px;">{{ $rental->movie->title }}</div>
                            <div style="font-size:12px; color:var(--text3);">{{ $rental->movie->genre }} · {{ $rental->days }} day(s)</div>
                            <div style="margin-top:8px; display:flex; align-items:center; gap:8px;">
                                @if($rental->isOverdue())
                                    <span class="badge badge-red">
                                        <span class="pulse" style="width:5px;height:5px;margin-right:3px;"></span>
                                        OVERDUE
                                    </span>
                                @else
                                    <span class="badge badge-red">
                                        <span class="pulse" style="width:5px;height:5px;margin-right:3px;"></span>
                                        Active
                                    </span>
                                @endif
                                <span style="font-size:11px; color:var(--text3);">Due: {{ $rental->due_date->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <div style="text-align:right; flex-shrink:0;">
                            <div style="font-size:16px; font-weight:700; color:var(--gold);">₱{{ number_format($rental->total_amount) }}</div>
                            <div style="font-size:11px; color:var(--text3); margin-top:2px;">{{ strtoupper($rental->payment_method) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="card" style="margin-bottom:22px; border-style:dashed;">
        <div style="padding:32px; text-align:center; color:var(--text3);">
            <div style="font-size:36px; margin-bottom:10px;">🎬</div>
            <div style="font-weight:500; margin-bottom:6px;">No active rentals</div>
            <div style="font-size:12px; margin-bottom:16px;">Browse our catalog and rent a movie today!</div>
            <a href="{{ route('user.movies.index') }}" class="btn btn-primary btn-sm">Browse Movies</a>
        </div>
    </div>
@endif

{{-- Featured Movies --}}
<div>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
        <div style="font-size:14px; font-weight:600;">✨ Available Now</div>
        <a href="{{ route('user.movies.index') }}" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(165px,1fr)); gap:14px;">
        @foreach($featuredMovies as $movie)
            <div class="card" style="overflow:hidden; cursor:pointer;" onclick="window.location='{{ route('user.movies.show', $movie) }}'">
                <div style="height:190px; position:relative; background:linear-gradient(145deg,var(--bg3),var(--bg4)); display:flex; align-items:center; justify-content:center; font-size:50px; overflow:hidden;">
                    @if($movie->poster_path)
                        <img src="{{ asset('storage/'.$movie->poster_path) }}" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <i class="{{ $movie->poster_icon }}"></i>
                    @endif
                    <div class="poster-watermark">
                        <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="rgba(200,160,74,.6)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        <span class="watermark-text">CineVault™</span>
                    </div>
                </div>
                <div style="padding:10px;">
                    <div style="font-size:12px; font-weight:600; margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $movie->title }}</div>
                    <div style="font-size:11px; color:var(--text3); margin-bottom:6px;">{{ $movie->genre }} · {{ $movie->year }}</div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:14px; font-weight:700; color:var(--gold);">₱{{ number_format($movie->price_per_day) }}<span style="font-size:10px; color:var(--text3); font-weight:400;">/day</span></span>
                        <a href="{{ route('user.movies.show', $movie) }}" class="btn btn-primary btn-sm" onclick="event.stopPropagation()">Rent</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Rental History --}}
@if($rentalHistory->count())
    <div style="margin-top:24px;">
        <div style="font-size:14px; font-weight:600; margin-bottom:14px;">📋 Recent Rental History</div>
        <div class="card" style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr><th>Movie</th><th>Rental Date</th><th>Return Date</th><th>Total</th><th>Payment</th></tr>
                </thead>
                <tbody>
                    @foreach($rentalHistory as $r)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:18px;"><i class="{{ $r->movie->poster_icon }}"></i></span>
                                    <span style="font-weight:500;">{{ $r->movie->title }}</span>
                                </div>
                            </td>
                            <td style="color:var(--text2);">{{ $r->rental_date->format('M d, Y') }}</td>
                            <td style="color:var(--text2);">{{ $r->returned_date ? $r->returned_date->format('M d, Y') : $r->due_date->format('M d, Y') }}</td>
                            <td style="font-weight:600; color:var(--gold);">₱{{ number_format($r->total_amount) }}</td>
                            <td><span class="badge {{ $r->payment_method==='gcash' ? 'badge-blue' : ($r->payment_method==='card' ? 'badge-purple' : 'badge-green') }}">{{ strtoupper($r->payment_method) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="text-align:center; margin-top:10px;">
            <a href="{{ route('user.rentals.index') }}" class="btn btn-secondary btn-sm">View Full History</a>
        </div>
    </div>
@endif
@endsection