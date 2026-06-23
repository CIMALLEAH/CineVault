@extends('layouts.app')
@section('title', 'My Requests')
@section('page-title', 'My Approval Requests')

@section('content')
<div class="card" style="overflow-x:auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Details</th>
                <th>Reason</th>
                <th>Submitted</th>
                <th>Status</th>
                <th>Admin Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($approvals as $approval)
                <tr>
                    <td>
                        <span class="badge {{ $approval->type === 'add_movie' ? 'badge-green' : 'badge-red' }}">
                            {{ $approval->getTypeLabel() }}
                        </span>
                    </td>
                    <td>
                        @if($approval->type === 'add_movie' && $approval->payload)
                            <div style="font-weight:500;">{{ $approval->payload['title'] ?? '—' }}</div>
                            <div style="font-size:11px; color:var(--text3);">
                                {{ $approval->payload['genre'] ?? '' }} · ₱{{ $approval->payload['price_per_day'] ?? '' }}/day
                            </div>
                        @elseif($approval->movie)
                            <div style="font-weight:500;">{{ $approval->movie->title }}</div>
                        @else
                            <span style="color:var(--text3);">—</span>
                        @endif
                    </td>
                    <td style="font-size:12px; color:var(--text2);">{{ $approval->reason ?? '—' }}</td>
                    <td style="font-size:12px; color:var(--text3);">{{ $approval->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        <span class="badge {{ $approval->status==='pending' ? 'badge-gold' : ($approval->status==='approved' ? 'badge-green' : 'badge-red') }}">
                            {{ ucfirst($approval->status) }}
                        </span>
                    </td>
                    <td style="font-size:12px; color:var(--text3);">
                        {{ $approval->admin_note ?? ($approval->status === 'pending' ? 'Waiting for review...' : '—') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px; color:var(--text3);">
                        No requests submitted yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">{{ $approvals->links() }}</div>
@endsection