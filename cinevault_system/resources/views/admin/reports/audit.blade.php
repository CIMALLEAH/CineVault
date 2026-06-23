@extends('layouts.app')
@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')
 
@section('content')
<form method="GET" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
    <select name="action" class="form-select" style="width:auto;" onchange="this.form.submit()">
        <option value="">All Actions</option>
        @foreach(['MOVIE_ADDED','MOVIE_UPDATED','MOVIE_DELETED','RENTAL_CREATED','RENTAL_RETURNED','APPROVAL_REQUESTED','APPROVAL_APPROVED','APPROVAL_REJECTED','USER_CREATED','USER_DELETED'] as $action)
            <option value="{{ $action }}" {{ request('action')===$action ? 'selected':'' }}>{{ str_replace('_',' ',$action) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
    <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary btn-sm">Clear</a>
</form>
 
<div class="card">
    @foreach($logs as $log)
        @php
            $colorMap = [
                'MOVIE_ADDED'=>'green','MOVIE_UPDATED'=>'blue','MOVIE_DELETED'=>'red',
                'RENTAL_CREATED'=>'blue','RENTAL_RETURNED'=>'green',
                'APPROVAL_APPROVED'=>'green','APPROVAL_REJECTED'=>'red','APPROVAL_REQUESTED'=>'gold',
                'USER_CREATED'=>'green','USER_DELETED'=>'red',
            ];
            $color = $colorMap[$log->action] ?? 'gold';
        @endphp
        <div style="display:flex; align-items:flex-start; gap:12px; padding:12px 20px; border-bottom:1px solid var(--border);">
            <div style="width:28px; height:28px; border-radius:50%; background:rgba({{ $color==='green'?'82,192,122':($color==='red'?'224,82,82':($color==='blue'?'82,148,224':'200,160,74')) }},.12); display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">
                <span style="font-size:11px; color:var(--{{ $color==='gold' ? 'gold' : $color }});">●</span>
            </div>
            <div style="flex:1;">
                <div style="font-size:13px; font-weight:500;">{{ $log->description }}</div>
                <div style="font-size:11px; color:var(--text3); margin-top:2px;">
                    by {{ $log->user?->name ?? 'System' }}
                    @if($log->ip_address) · {{ $log->ip_address }} @endif
                </div>
            </div>
            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px; flex-shrink:0;">
                <span class="badge badge-{{ $color==='gold'?'gold':$color }}" style="font-size:9px;">{{ $log->action }}</span>
                <span style="font-size:11px; color:var(--text3);">{{ $log->created_at->format('M d, Y H:i') }}</span>
            </div>
        </div>
    @endforeach
    @if($logs->isEmpty())
        <div style="text-align:center; padding:40px; color:var(--text3);">No audit logs found.</div>
    @endif
</div>
<div class="pagination">{{ $logs->links() }}</div>
@endsection