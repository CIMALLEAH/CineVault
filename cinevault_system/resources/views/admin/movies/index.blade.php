@extends('layouts.app')
@section('title', 'Movies')
@section('page-title', 'Movie Catalog')
 
@section('topbar-actions')
    <a href="{{ route('admin.movies.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Movie
    </a>
@endsection
 
@section('content')
{{-- Filters --}}
<div style="display:flex; align-items:center; gap:10px; margin-bottom:16px; flex-wrap:wrap;">
    <form method="GET" style="display:flex; gap:8px; flex-wrap:wrap; flex:1;">
        <div class="search-box" style="flex:1; min-width:200px;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" placeholder="Search title..." value="{{ request('search') }}" style="width:100%;">
        </div>
 
        <select name="genre" class="form-select" style="width:auto;" onchange="this.form.submit()">
            <option value="">All Genres</option>
            @foreach($genres as $g)
                <option value="{{ $g }}" {{ request('genre') === $g ? 'selected' : '' }}>{{ $g }}</option>
            @endforeach
        </select>
 
        <select name="status" class="form-select" style="width:auto;" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
            <option value="rented"    {{ request('status') === 'rented'    ? 'selected' : '' }}>Rented</option>
        </select>
 
        <select name="sort" class="form-select" style="width:auto;" onchange="this.form.submit()">
            <option value="title"         {{ request('sort') === 'title'         ? 'selected' : '' }}>A–Z</option>
            <option value="price_per_day" {{ request('sort') === 'price_per_day' ? 'selected' : '' }}>Price</option>
            <option value="year"          {{ request('sort') === 'year'          ? 'selected' : '' }}>Year</option>
        </select>
    </form>
</div>
 
{{-- Movie Grid --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(175px,1fr)); gap:16px;">
    @forelse($movies as $movie)
        @php $hasRental = $movie->hasActiveRental(); @endphp
        <div class="card" style="overflow:hidden; transition:transform .2s; cursor:pointer;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
 
            {{-- Poster --}}
            <div style="height:210px; position:relative; background:linear-gradient(145deg,var(--bg3),var(--bg4)); display:flex; align-items:center; justify-content:center; font-size:56px; overflow:hidden;">
                @if($movie->poster_path)
                    <img src="{{ asset('storage/'.$movie->poster_path) }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    {{ $movie->poster_emoji }}
                @endif
 
                {{-- Status badge --}}
                <span class="badge {{ $movie->status === 'available' ? 'badge-green' : ($movie->status === 'rented' ? 'badge-red' : 'badge-blue') }}"
                      style="position:absolute; top:8px; left:8px;">
                    {{ ucfirst($movie->status) }}
                </span>
 
                {{-- Live rental pulse --}}
                @if($hasRental)
                    <span class="pulse" style="position:absolute; top:10px; right:10px;"></span>
                @endif
 
                {{-- Watermark --}}
                <div class="poster-watermark">
                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="rgba(200,160,74,.6)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span class="watermark-text">CineVault™</span>
                </div>
            </div>
 
            {{-- Info --}}
            <div style="padding:12px;">
                <div style="font-size:13px; font-weight:600; margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $movie->title }}">{{ $movie->title }}</div>
                <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text3); margin-bottom:8px;">
                    <span>{{ $movie->genre }}</span>
                    <span>{{ $movie->year }}</span>
                </div>
                <div style="font-size:15px; font-weight:700; color:var(--gold); margin-bottom:10px;">₱{{ number_format($movie->price_per_day) }}<span style="font-size:10px; color:var(--text3); font-weight:400;">/day</span></div>
 
                <div style="display:flex; gap:6px;">
                    @if($movie->status === 'available')
                        <a href="{{ route('admin.rentals.create') }}?movie_id={{ $movie->id }}" class="btn btn-primary btn-sm" style="flex:1; justify-content:center;">Rent</a>
                    @else
                        <span class="btn btn-secondary btn-sm" style="flex:1; justify-content:center; opacity:.5; cursor:not-allowed;">{{ ucfirst($movie->status) }}</span>
                    @endif
 
                    <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-secondary btn-sm">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
 
                    @if(!$hasRental)
                        <form method="POST" action="{{ route('admin.movies.destroy', $movie) }}" onsubmit="return confirm('Delete {{ addslashes($movie->title) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                            </button>
                        </form>
                    @else
                        <span class="btn btn-danger btn-sm" style="opacity:.35; cursor:not-allowed;" title="Has active rental">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column:1/-1; text-align:center; padding:60px; color:var(--text3);">
            <div style="font-size:48px; margin-bottom:12px;">🎬</div>
            <div>No movies found. <a href="{{ route('admin.movies.create') }}" style="color:var(--gold);">Add one!</a></div>
        </div>
    @endforelse
</div>
 
{{-- Pagination --}}
<div class="pagination">{{ $movies->links() }}</div>
@endsection
