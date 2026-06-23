@extends('layouts.app')
@section('title', 'Browse Movies')
@section('page-title', 'Browse Movies')

@section('content')
{{-- Filters --}}
<form method="GET" style="display:flex; gap:8px; margin-bottom:18px; flex-wrap:wrap;">
    <div class="search-box" style="flex:1; min-width:200px;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" placeholder="Search movies..." value="{{ request('search') }}">
    </div>
    <select name="genre" class="form-select" style="width:auto;" onchange="this.form.submit()">
        <option value="">All Genres</option>
        @foreach($genres as $g)
            <option value="{{ $g }}" {{ request('genre')===$g ? 'selected':'' }}>{{ $g }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select" style="width:auto;" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="available" {{ request('status')==='available' ? 'selected':'' }}>Available Only</option>
    </select>
    <select name="sort" class="form-select" style="width:auto;" onchange="this.form.submit()">
        <option value="title"         {{ request('sort')==='title'         ? 'selected':'' }}>A–Z</option>
        <option value="price_per_day" {{ request('sort')==='price_per_day' ? 'selected':'' }}>Price</option>
        <option value="year"          {{ request('sort')==='year'          ? 'selected':'' }}>Year</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm">Search</button>
</form>

{{-- Genre chips --}}
<div style="display:flex; gap:8px; margin-bottom:18px; flex-wrap:wrap;">
    <a href="{{ request()->fullUrlWithQuery(['genre'=>'']) }}"
       class="btn btn-sm {{ !request('genre') ? 'btn-primary' : 'btn-secondary' }}">All</a>
    @foreach($genres as $g)
        <a href="{{ request()->fullUrlWithQuery(['genre'=>$g]) }}"
           class="btn btn-sm {{ request('genre')===$g ? 'btn-primary' : 'btn-secondary' }}">{{ $g }}</a>
    @endforeach
</div>

{{-- Movie Grid --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(175px,1fr)); gap:16px;">
    @forelse($movies as $movie)
        <div class="card" style="overflow:hidden; transition:transform .2s; cursor:pointer;"
             onmouseover="this.style.transform='translateY(-3px)'"
             onmouseout="this.style.transform=''"
             onclick="window.location='{{ route('user.movies.show', $movie) }}'">

            {{-- Poster --}}
            <div style="height:210px; position:relative; background:linear-gradient(145deg,var(--bg3),var(--bg4)); display:flex; align-items:center; justify-content:center; font-size:56px; overflow:hidden;">
                @if($movie->poster_path)
                    <img src="{{ asset('storage/'.$movie->poster_path) }}" style="width:100%; height:100%; object-fit:cover;">
                @else
                    <i class="{{ $movie->poster_icon }}"></i>
                @endif

                <span class="badge {{ $movie->status==='available' ? 'badge-green' : 'badge-red' }}"
                      style="position:absolute; top:8px; left:8px;">
                    {{ ucfirst($movie->status) }}
                </span>

                <span class="badge badge-gold" style="position:absolute; top:8px; right:8px; font-size:9px;">
                    {{ $movie->rating }}
                </span>

                <div class="poster-watermark">
                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="rgba(200,160,74,.6)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span class="watermark-text">CineVault™</span>
                </div>
            </div>

            {{-- Info --}}
            <div style="padding:12px;">
                <div style="font-size:13px; font-weight:600; margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $movie->title }}">
                    {{ $movie->title }}
                </div>
                <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text3); margin-bottom:8px;">
                    <span>{{ $movie->genre }}</span>
                    <span>{{ $movie->year }}</span>
                </div>
                <div style="font-size:15px; font-weight:700; color:var(--gold); margin-bottom:10px;">
                    ₱{{ number_format($movie->price_per_day) }}<span style="font-size:10px; color:var(--text3); font-weight:400;">/day</span>
                </div>
                @if($movie->status === 'available')
                    <a href="{{ route('user.movies.show', $movie) }}" class="btn btn-primary btn-sm" style="width:100%; justify-content:center;" onclick="event.stopPropagation()">
                        Rent Now
                    </a>
                @else
                    <span class="btn btn-secondary btn-sm" style="width:100%; justify-content:center; opacity:.5; cursor:not-allowed;">
                        Not Available
                    </span>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column:1/-1; text-align:center; padding:60px; color:var(--text3);">
            <div style="font-size:48px; margin-bottom:12px;">🎬</div>
            <div>No movies found matching your search.</div>
        </div>
    @endforelse
</div>

<div class="pagination">{{ $movies->links() }}</div>
@endsection