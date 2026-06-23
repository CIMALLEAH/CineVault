@extends('layouts.app')
@section('title', 'Approvals')
@section('page-title', 'Pending Approvals')
 
@section('content')
<div class="card" style="overflow-x:auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Requested By</th>
                <th>Details</th>
                <th>Reason</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
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
                        <div style="font-weight:500;">{{ $approval->requester->name }}</div>
                        <span class="badge badge-blue" style="margin-top:3px; font-size:9px;">{{ $approval->requester->role }}</span>
                    </td>
                    <td>
                        @if($approval->type === 'add_movie' && $approval->payload)
                            <div style="font-weight:500;">{{ $approval->payload['title'] ?? '—' }}</div>
                            <div style="font-size:11px; color:var(--text3);">{{ $approval->payload['genre'] ?? '' }} · ₱{{ $approval->payload['price_per_day'] ?? '' }}/day</div>
                        @elseif($approval->movie)
                            <div style="font-weight:500;">{{ $approval->movie->title }}</div>
                        @else
                            <span style="color:var(--text3);">—</span>
                        @endif
                    </td>
                    <td style="color:var(--text2); font-size:12px;">{{ $approval->reason ?? '—' }}</td>
                    <td style="color:var(--text3); font-size:12px;">{{ $approval->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        <span class="badge {{ $approval->status === 'pending' ? 'badge-gold' : ($approval->status === 'approved' ? 'badge-green' : 'badge-red') }}">
                            {{ ucfirst($approval->status) }}
                        </span>
                    </td>
                    <td>
                        @if($approval->status === 'pending')
                            <div style="display:flex; gap:6px;">
                                <form method="POST" action="{{ route('admin.approvals.approve', $approval) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">✓ Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.approvals.reject', $approval) }}" onsubmit="return confirm('Reject this request?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-danger btn-sm">✗ Reject</button>
                                </form>
                            </div>
                        @else
                            <div style="font-size:11px; color:var(--text3);">
                                Reviewed {{ $approval->reviewed_at?->format('M d') }} by {{ $approval->reviewer?->name }}
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text3);">No approval requests.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="pagination">{{ $approvals->links() }}</div>
@endsection