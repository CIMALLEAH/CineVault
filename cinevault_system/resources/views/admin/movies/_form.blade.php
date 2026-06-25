{{-- Shared form partial for admin create and edit --}}
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

    {{-- Copies --}}
    <div class="form-group">
        <label class="form-label">Total Copies *</label>
        <input class="form-input" type="number" name="copies" value="{{ old('copies', $movie->copies ?? 1) }}" required min="1">
        <div style="font-size:11px; color:var(--text3); margin-top:4px;">Total physical copies in inventory.</div>
    </div>

    <div class="form-group">
        <label class="form-label">Icon</label>
        <input class="form-input" type="text" name="poster_icon" value="{{ old('poster_icon', $movie->poster_icon ?? '🎬') }}" placeholder="🎬" maxlength="10">
        <div style="font-size:11px; color:var(--text3); margin-top:4px;">Used when no image is uploaded.</div>
    </div>

    {{-- Pricing section --}}
    <div style="grid-column:1/-1; border-top:1px solid var(--border); padding-top:14px; margin-top:4px;">
        <div style="font-size:12px; font-weight:600; color:var(--text3); margin-bottom:12px; text-transform:uppercase; letter-spacing:.5px;">Rental Pricing</div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div class="form-group">
                <label class="form-label">🎬 One-Time Screening (₱)</label>
                <input class="form-input" type="number" name="price_per_screening"
                       value="{{ old('price_per_screening', $movie->price_per_screening ?? '') }}"
                       step="0.01" min="0" placeholder="Auto (1.5× daily)">
                <div style="font-size:10px; color:var(--text3); margin-top:3px;">Watch once — like a cinema ticket. Leave blank to auto-calculate.</div>
            </div>
            <div class="form-group">
                <label class="form-label">📅 Price Per Day (₱) *</label>
                <input class="form-input" type="number" name="price_per_day"
                       value="{{ old('price_per_day', $movie->price_per_day ?? 50) }}"
                       required step="0.01" min="0" id="ppd-input" oninput="updatePriceHints()">
                <div style="font-size:10px; color:var(--text3); margin-top:3px;">Daily rental rate.</div>
            </div>
            <div class="form-group">
                <label class="form-label">📆 Price Per Week (₱)</label>
                <input class="form-input" type="number" name="price_per_week"
                       value="{{ old('price_per_week', $movie->price_per_week ?? '') }}"
                       step="0.01" min="0" placeholder="Auto (5× daily)">
                <div style="font-size:10px; color:var(--text3); margin-top:3px;">7-day rental rate. Leave blank to auto-calculate.</div>
            </div>
        </div>
        <div id="price-hints" style="font-size:11px; color:var(--text3); padding:8px 12px; background:rgba(200,160,74,.05); border-radius:6px; border:1px solid rgba(200,160,74,.12); margin-top:4px;"></div>
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

<script>
function updatePriceHints() {
    const ppd = parseFloat(document.getElementById('ppd-input').value) || 0;
    const screening = Math.round(ppd * 1.5 * 100) / 100;
    const weekly    = Math.round(ppd * 5 * 100) / 100;
    document.getElementById('price-hints').innerHTML =
        `Auto-calculated prices if left blank — Screening: <strong>₱${screening.toLocaleString()}</strong> &nbsp;|&nbsp; Weekly: <strong>₱${weekly.toLocaleString()}</strong>`;
}
updatePriceHints();
</script>