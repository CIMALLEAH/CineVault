{{-- Shared form partial for create and edit --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
    <div class="form-group" style="grid-column:1/-1;">
        <label class="form-label">Title *</label>
        <input class="form-input" type="text" name="title" value="{{ old('title', $movie->title ?? '') }}" required placeholder="Movie title">
    </div>
 
    <div class="form-group">
        <label class="form-label">Genre *</label>
        <select class="form-select" name="genre" required>
            @foreach(['Action','Animation','Comedy','Drama','Horror','Romance','Sci-Fi','Thriller'] as $g)
                <option value="{{ $g }}" {{ old('genre', $movie->genre ?? '') === $g ? 'selected' : '' }}>{{ $g }}</option>
            @endforeach
        </select>
    </div>
 
    <div class="form-group">
        <label class="form-label">Year *</label>
        <input class="form-input" type="number" name="year" value="{{ old('year', $movie->year ?? date('Y')) }}" required min="1900" max="{{ date('Y') + 2 }}">
    </div>
 
    <div class="form-group">
        <label class="form-label">Director</label>
        <input class="form-input" type="text" name="director" value="{{ old('director', $movie->director ?? '') }}" placeholder="Director name">
    </div>
 
    <div class="form-group">
        <label class="form-label">Duration (minutes)</label>
        <input class="form-input" type="number" name="duration" value="{{ old('duration', $movie->duration ?? '') }}" placeholder="120" min="1">
    </div>
 
    <div class="form-group">
        <label class="form-label">Rating *</label>
        <select class="form-select" name="rating" required>
            @foreach(['G','PG','PG-13','R'] as $r)
                <option value="{{ $r }}" {{ old('rating', $movie->rating ?? 'PG') === $r ? 'selected' : '' }}>{{ $r }}</option>
            @endforeach
        </select>
    </div>
 
    <div class="form-group">
        <label class="form-label">Price Per Day (₱) *</label>
        <input class="form-input" type="number" name="price_per_day" value="{{ old('price_per_day', $movie->price_per_day ?? 50) }}" required step="0.01" min="1">
    </div>
 
    <div class="form-group">
        <label class="form-label">Emoji Icon</label>
        <input class="form-input" type="text" name="poster_emoji" value="{{ old('poster_emoji', $movie->poster_emoji ?? '🎬') }}" placeholder="🎬" maxlength="10">
        <div style="font-size:11px; color:var(--text3); margin-top:4px;">Used when no image is uploaded.</div>
    </div>
 
    <div class="form-group" style="grid-column:1/-1;">
        <label class="form-label">Poster Image</label>
        <input class="form-input" type="file" name="poster" accept="image/*" style="padding:7px;">
        @if(!empty($movie->poster_path))
            <div style="margin-top:8px; display:flex; align-items:center; gap:8px;">
                <img src="{{ asset('storage/'.$movie->poster_path) }}" style="width:60px; height:80px; object-fit:cover; border-radius:4px; border:1px solid var(--border);">
                <span style="font-size:11px; color:var(--text3);">Current poster. Upload new to replace.</span>
            </div>
        @endif
    </div>
 
    <div class="form-group" style="grid-column:1/-1;">
        <label class="form-label">Description</label>
        <textarea class="form-textarea" name="description" rows="3" placeholder="Movie synopsis...">{{ old('description', $movie->description ?? '') }}</textarea>
    </div>
</div>