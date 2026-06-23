@extends('layouts.app')
@section('title', 'Rentals')
@section('page-title', 'Rental Management')

@section('topbar-actions')
    <a href="{{ route('staff.rentals.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Rental
    </a>
@endsection

@section('content')
{{-- Tabs --}}
<div style="display:flex; border-bottom:1px solid var(--border); margin-bottom:20px;">
    @foreach(['active'=>'Active Rentals','history'=>'Return History'] as $key=>$label)
        <a href="{{ request()->fullUrlWithQuery(['tab'=>$key]) }}"
           style="padding:9px 18px; font-size:13px; color:{{ $tab===$key ? 'var(--gold)' : 'var(--text3)' }}; border-bottom:2px solid {{ $tab===$key ? 'var(--gold)' : 'transparent' }}; text-decoration:none; margin-bottom:-1px;">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="card" style="overflow-x:auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Movie</th>
                <th>Customer</th>
                <th>Days</th>
                <th>Rental Date</th>
                <th>Due Date</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                @if($tab === 'active')<th>Action</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($rentals as $rental)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:20px;">{{ $rental->movie->poster_icon }}</span>
                            <div>
                                <div style="font-weight:500;">{{ $rental->movie->title }}</div>
                                <div style="font-size:11px; color:var(--text3);">{{ $rental->movie->genre }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:500;">{{ $rental->customer_name }}</div>
                        <div style="font-size:11px; color:var(--text3);">{{ $rental->customer_contact }}</div>
                    </td>
                    <td>{{ $rental->days }}d</td>
                    <td style="color:var(--text2);">{{ $rental->rental_date->format('M d, Y') }}</td>
                    <td>
                        <span style="color:{{ $rental->isOverdue() ? 'var(--red)' : 'var(--text)' }};">
                            {{ $rental->due_date->format('M d, Y') }}
                            @if($rental->isOverdue())
                                <span class="badge badge-red" style="margin-left:4px; font-size:9px;">Overdue</span>
                            @endif
                        </span>
                    </td>
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
                            <span class="badge badge-red"><span class="pulse" style="width:5px;height:5px;margin-right:3px;"></span>Active</span>
                        @elseif($rental->status === 'overdue')
                            <span class="badge badge-red">Overdue</span>
                        @else
                            <span class="badge badge-green">Returned</span>
                        @endif
                    </td>
                    @if($tab === 'active')
                        <td>
                            @if(in_array($rental->status, ['active','overdue']))
                                <form method="POST" action="{{ route('staff.rentals.return', $rental) }}" onsubmit="return confirm('Mark as returned?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                        Return
                                    </button>
                                </form>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:40px; color:var(--text3);">No rentals found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">{{ $rentals->links() }}</div>
@endsection