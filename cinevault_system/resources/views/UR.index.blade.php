@extends('layouts.app')
@section('title', 'My Rentals')
@section('page-title', 'My Rental History')

@section('content')
<div class="card" style="overflow-x:auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Movie</th>
                <th>Rental Date</th>
                <th>Due Date</th>
                <th>Returned</th>
                <th>Days</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rentals as $rental)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="font-size:22px;">{{ $rental->movie->poster_emoji }}</span>
                            <div>
                                <div style="font-weight:500; font-size:13px;">{{ $rental->movie->title }}</div>
                                <div style="font-size:11px; color:var(--text3);">{{ $rental->movie->genre }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--text2);">{{ $rental->rental_date->format('M d, Y') }}</td>
                    <td>
                        <span style="color:{{ $rental->isOverdue() ? 'var(--red)' : 'var(--text2)' }};">
                            {{ $rental->due_date->format('M d, Y') }}
                        </span>
                    </td>
                    <td style="color:var(--text2);">
                        {{ $rental->returned_date ? $rental->returned_date->format('M d, Y') : '—' }}
                    </td>
                    <td>{{ $rental->days }}d</td>
                    <td style="font-weight:700; color:var(--gold);">₱{{ number_format($rental->total_amount) }}</td>
                    <td>
                        <span class="badge {{ $rental->payment_method==='gcash' ? 'badge-blue' : ($rental->payment_method==='card' ? 'badge-purple' : 'badge-green') }}">
                            {{ strtoupper($rental->payment_method) }}
                        </span>
                        @if($rental->payment_reference)
                            <div style="font-size:10px; color:var(--text3); margin-top:2px;">{{ $rental->payment_reference }}</div>
                        @endif
                    </td>
                    <td>
                        @if($rental->status === 'active')
                            <span class="badge badge-red">
                                <span class="pulse" style="width:5px;height:5px;margin-right:3px;"></span>Active
                            </span>
                        @elseif($rental->status === 'overdue')
                            <span class="badge badge-red">Overdue</span>
                        @else
                            <span class="badge badge-green">Returned</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:60px; color:var(--text3);">
                        <div style="font-size:36px; margin-bottom:10px;">🎬</div>
                        <div style="margin-bottom:12px;">You haven't rented any movies yet.</div>
                        <a href="{{ route('user.movies.index') }}" class="btn btn-primary btn-sm">Browse Movies</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pagination">{{ $rentals->links() }}</div>
@endsection