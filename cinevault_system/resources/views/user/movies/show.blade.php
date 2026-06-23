@extends('layouts.app')
@section('title', $movie->title)
@section('page-title', $movie->title)

@section('content')
<div style="display:grid; grid-template-columns:300px 1fr; gap:24px; max-width:900px;">

    {{-- Poster --}}
    <div>
        <div style="height:380px; position:relative; background:linear-gradient(145deg,var(--bg3),var(--bg4)); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:80px; overflow:hidden; border:1px solid var(--border);">
            @if($movie->poster_path)
                <img src="{{ asset('storage/'.$movie->poster_path) }}" style="width:100%; height:100%; object-fit:cover;">
            @else
                <i class="{{ $movie->poster_icon }}"></i>
            @endif
            <span class="badge {{ $movie->status==='available' ? 'badge-green' : 'badge-red' }}" style="position:absolute; top:12px; left:12px; font-size:12px;">
                {{ ucfirst($movie->status) }}
            </span>
            <div class="poster-watermark">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="rgba(200,160,74,.6)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                <span class="watermark-text" style="font-size:10px;">CineVault™</span>
            </div>
        </div>

        {{-- Movie meta --}}
        <div class="card" style="margin-top:14px;">
            <div style="padding:14px;">
                <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text3);">Genre</span>
                        <span class="badge badge-gold">{{ $movie->genre }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text3);">Year</span>
                        <span>{{ $movie->year }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text3);">Director</span>
                        <span>{{ $movie->director ?? '—' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text3);">Duration</span>
                        <span>{{ $movie->duration ? $movie->duration . ' min' : '—' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text3);">Rating</span>
                        <span class="badge badge-purple">{{ $movie->rating }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding-top:8px; border-top:1px solid var(--border); margin-top:2px;">
                        <span style="color:var(--text3); font-weight:500;">Price per day</span>
                        <span style="font-size:16px; font-weight:700; color:var(--gold);">₱{{ number_format($movie->price_per_day) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Details + Rent Form --}}
    <div>
        <h1 style="font-size:22px; font-weight:700; margin-bottom:6px;">{{ $movie->title }}</h1>
        <div style="font-size:13px; color:var(--text2); line-height:1.7; margin-bottom:20px;">
            {{ $movie->description ?? 'No description available.' }}
        </div>

        @if($movie->status === 'available')
            <div class="card">
                <div class="card-body">
                    <div style="font-size:14px; font-weight:600; margin-bottom:16px; color:var(--text2);">
                        🎬 Rent This Movie
                    </div>
                    <form method="POST" action="{{ route('user.rentals.store') }}">
                        @csrf
                        <input type="hidden" name="movie_id" value="{{ $movie->id }}">

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="form-group">
                                <label class="form-label">Rental Days *</label>
                                <input class="form-input" type="number" name="days" id="days-input"
                                       value="{{ old('days', 1) }}" min="1" max="14" required oninput="updateTotal()">
                                <div style="font-size:11px; color:var(--text3); margin-top:3px;">Max 14 days</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Payment Method *</label>
                                <select class="form-select" name="payment_method" required onchange="toggleRef(this.value)">
                                    <option value="cash"  {{ old('payment_method')==='cash'  ? 'selected':'' }}>Cash</option>
                                    <option value="gcash" {{ old('payment_method')==='gcash' ? 'selected':'' }}>GCash</option>
                                    <option value="card"  {{ old('payment_method')==='card'  ? 'selected':'' }}>Credit/Debit Card</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="ref-group" style="display:none;">
                            <label class="form-label">Reference Number</label>
                            <input class="form-input" type="text" name="payment_reference"
                                   value="{{ old('payment_reference') }}" placeholder="GCash ref / last 4 digits">
                        </div>

                        {{-- Price summary --}}
                        <div style="padding:14px; background:rgba(200,160,74,.07); border:1px solid rgba(200,160,74,.18); border-radius:6px; margin-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
                                <span style="color:var(--text2);">Price per day:</span>
                                <span>₱{{ number_format($movie->price_per_day) }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
                                <span style="color:var(--text2);">Days:</span>
                                <span id="days-display">1</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:700; padding-top:8px; border-top:1px solid rgba(200,160,74,.18); margin-top:8px;">
                                <span style="color:var(--gold);">Total:</span>
                                <span style="color:var(--gold);" id="total-display">₱{{ number_format($movie->price_per_day) }}</span>
                            </div>
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center; padding:12px;">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                Confirm Rental
                            </button>
                            <a href="{{ route('user.movies.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="card" style="border-color:rgba(224,82,82,.3);">
                <div style="padding:24px; text-align:center;">
                    <div style="font-size:36px; margin-bottom:10px;">😔</div>
                    <div style="font-weight:600; margin-bottom:6px;">Currently Not Available</div>
                    <div style="font-size:13px; color:var(--text3); margin-bottom:16px;">This movie is currently rented out. Check back later!</div>
                    <a href="{{ route('user.movies.index') }}" class="btn btn-secondary">Browse Other Movies</a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
const pricePerDay = {{ $movie->price_per_day }};

function updateTotal() {
    const days = parseInt(document.getElementById('days-input').value) || 1;
    document.getElementById('days-display').textContent = days;
    document.getElementById('total-display').textContent = '₱' + (pricePerDay * days).toLocaleString('en-PH', {minimumFractionDigits:0});
}

function toggleRef(method) {
    document.getElementById('ref-group').style.display = (method === 'gcash' || method === 'card') ? '' : 'none';
}
</script>
@endsection