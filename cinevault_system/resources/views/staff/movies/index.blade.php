@extends('layouts.app')
@section('title', 'Movies')
@section('page-title', 'Movie Catalog')

@section('topbar-actions')
    <button onclick="document.getElementById('add-modal').style.display='flex'" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Request Add Movie
    </button>
@endsection

@section('content')
{{-- Info banner --}}
<div class="alert" style="background:rgba(82,148,224,.08); border:1px solid rgba(82,148,224,.2); color:var(--blue); margin-bottom:16px;">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    As Staff, you can add movies (requires admin approval) but cannot delete them directly. To delete, use the Request Delete option.
</div>

{{-- Filters --}}
<form method="GET" style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap;">
    <div class="search-box" style="flex:1; min-width:180px;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
    </div>
    <select name="genre" class="form-select" style="width:auto;" onchange="this.form.submit()">
        <option value="">All Genres</option>
        @foreach($genres as $g)
            <option value="{{ $g }}" {{ request('genre')===$g ? 'selected':'' }}>{{ $g }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select" style="width:auto;" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="available" {{ request('status')==='available'?'selected':'' }}>Available</option>
        <option value="rented"    {{ request('status')==='rented'   ?'selected':'' }}>Rented</option>
    </select>
</form>

{{-- Movie Grid --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(175px,1fr)); gap:16px;">
    @forelse($movies as $movie)
        @php $hasRental = $movie->hasActiveRental(); @endphp
        <div class="card" style="overflow:hidden;">
            <div style="height:200px; position:relative; background:linear-gradient(145deg,var(--bg3),var(--bg4)); display:flex; align-items:center; justify-content:center; font-size:52px; overflow:hidden;">
                @if($movie->poster_path)
                    <img src="{{ asset('storage/'.$movie->poster_path) }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    {{ $movie->poster_emoji }}
                @endif
                <span class="badge {{ $movie->status==='available' ? 'badge-green' : 'badge-red' }}" style="position:absolute; top:8px; left:8px;">{{ ucfirst($movie->status) }}</span>
                @if($hasRental)<span class="pulse" style="position:absolute; top:10px; right:10px;"></span>@endif
                <div class="poster-watermark">
                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="rgba(200,160,74,.6)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span class="watermark-text">CineVault™</span>
                </div>
            </div>
            <div style="padding:12px;">
                <div style="font-size:13px; font-weight:600; margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $movie->title }}</div>
                <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text3); margin-bottom:8px;">
                    <span>{{ $movie->genre }}</span><span>{{ $movie->year }}</span>
                </div>
                <div style="font-size:14px; font-weight:700; color:var(--gold); margin-bottom:10px;">₱{{ number_format($movie->price_per_day) }}<span style="font-size:10px; color:var(--text3); font-weight:400;">/day</span></div>
                <div style="display:flex; gap:5px;">
                    @if($movie->status === 'available')
                        <a href="{{ route('staff.rentals.create') }}?movie_id={{ $movie->id }}" class="btn btn-primary btn-sm" style="flex:1; justify-content:center;">Rent</a>
                    @else
                        <span class="btn btn-secondary btn-sm" style="flex:1; justify-content:center; opacity:.5; cursor:not-allowed;">Rented</span>
                    @endif
                    @if(!$hasRental)
                        <form method="POST" action="{{ route('staff.movies.request-delete', $movie) }}" onsubmit="return confirm('Submit delete request for admin approval?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" title="Request Delete">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
                            </button>
                        </form>
                    @else
                        <span class="btn btn-danger btn-sm" style="opacity:.3; cursor:not-allowed;" title="Has active rental">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column:1/-1; text-align:center; padding:60px; color:var(--text3);">
            <div style="font-size:48px; margin-bottom:12px;">🎬</div>
            <div>No movies found.</div>
        </div>
    @endforelse
</div>

<div class="pagination">{{ $movies->links() }}</div>

{{-- Add Movie Modal --}}
<div id="add-modal" class="modal-overlay" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <div style="font-size:15px; font-weight:600;">Request: Add Movie</div>
            <button onclick="document.getElementById('add-modal').style.display='none'" style="background:none; border:none; color:var(--text3); cursor:pointer; font-size:20px; line-height:1;">×</button>
        </div>
        <form method="POST" action="{{ route('staff.movies.request-add') }}">
            @csrf
            <div class="modal-body">
                <div class="alert" style="background:rgba(82,148,224,.08); border:1px solid rgba(82,148,224,.2); color:var(--blue); margin-bottom:16px; font-size:12px;">
                    This request will be sent to the admin for approval before the movie appears in the catalog.
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Title *</label>
                        <input class="form-input" type="text" name="title" required placeholder="Movie title">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Genre *</label>
                        <select class="form-select" name="genre" required>
                            @foreach(['Action','Animation','Comedy','Drama','Horror','Romance','Sci-Fi','Thriller'] as $g)
                                <option value="{{ $g }}">{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Year *</label>
                        <input class="form-input" type="number" name="year" value="{{ date('Y') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price/Day (₱) *</label>
                        <input class="form-input" type="number" name="price_per_day" value="50" required step="0.01" min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rating *</label>
                        <select class="form-select" name="rating" required>
                            <option>G</option><option>PG</option><option selected>PG-13</option><option>R</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Director</label>
                        <input class="form-input" type="text" name="director" placeholder="Director name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Emoji Icon</label>
                        <input class="form-input" type="text" name="poster_emoji" value="🎬">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Reason / Notes</label>
                        <textarea class="form-textarea" name="reason" rows="2" placeholder="Why should this be added?"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('add-modal').style.display='none'" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit for Approval</button>
            </div>
        </form>
    </div>
</div>
@endsection
