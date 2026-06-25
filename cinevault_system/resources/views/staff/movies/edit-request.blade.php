@extends('layouts.app')
@section('title', 'Request Edit: ' . $movie->title)
@section('page-title', 'Request Edit: ' . $movie->title)

@section('content')
<div style="max-width:640px;">
    <div class="alert" style="background:rgba(82,148,224,.08); border:1px solid rgba(82,148,224,.2); color:var(--blue); margin-bottom:16px;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        This edit request will be sent to an admin for review. The movie will not change until approved.
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('staff.movies.submit-edit', $movie) }}">
                @csrf
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Title *</label>
                        <input class="form-input" type="text" name="title" value="{{ old('title', $movie->title) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Genre *</label>
                        <select class="form-select" name="genre" required>
                            @foreach(['Action','Animation','Comedy','Drama','Horror','Romance','Sci-Fi','Thriller'] as $g)
                                <option value="{{ $g }}" {{ old('genre', $movie->genre) === $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Year *</label>
                        <input class="form-input" type="number" name="year" value="{{ old('year', $movie->year) }}" required min="1900" max="{{ date('Y') + 2 }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Director</label>
                        <input class="form-input" type="text" name="director" value="{{ old('director', $movie->director) }}" placeholder="Director name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (minutes)</label>
                        <input class="form-input" type="number" name="duration" value="{{ old('duration', $movie->duration) }}" min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rating *</label>
                        <select class="form-select" name="rating" required>
                            @foreach(['G','PG','PG-13','R'] as $r)
                                <option value="{{ $r }}" {{ old('rating', $movie->rating) === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Copies *</label>
                        <input class="form-input" type="number" name="copies" value="{{ old('copies', $movie->copies) }}" required min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price Per Day (₱) *</label>
                        <input class="form-input" type="number" name="price_per_day" value="{{ old('price_per_day', $movie->price_per_day) }}" required step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Screening Price (₱)</label>
                        <input class="form-input" type="number" name="price_per_screening" value="{{ old('price_per_screening', $movie->price_per_screening) }}" step="0.01" min="0" placeholder="Auto (1.5× daily)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weekly Price (₱)</label>
                        <input class="form-input" type="number" name="price_per_week" value="{{ old('price_per_week', $movie->price_per_week) }}" step="0.01" min="0" placeholder="Auto (5× daily)">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Description</label>
                        <textarea class="form-textarea" name="description" rows="3">{{ old('description', $movie->description) }}</textarea>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label">Reason for Edit *</label>
                        <textarea class="form-textarea" name="reason" rows="2" required placeholder="Explain what needs to be changed and why...">{{ old('reason') }}</textarea>
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:4px;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
                        Submit for Approval
                    </button>
                    <a href="{{ route('staff.movies.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection