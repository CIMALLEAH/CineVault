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
            @php $canRent = $movie->isAvailable(); @endphp
            <span class="badge {{ $canRent ? 'badge-green' : 'badge-red' }}" style="position:absolute; top:12px; left:12px; font-size:12px;">
                {{ $canRent ? $movie->available_copies . '/' . $movie->copies . ' Available' : 'All Rented' }}
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
                        <span style="color:var(--text3);">Year</span><span>{{ $movie->year }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text3);">Director</span><span>{{ $movie->director ?? '—' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text3);">Duration</span>
                        <span>{{ $movie->duration ? $movie->duration . ' min' : '—' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text3);">Rating</span>
                        <span class="badge badge-purple">{{ $movie->rating }}</span>
                    </div>

                    {{-- Pricing tiers --}}
                    <div style="border-top:1px solid var(--border); padding-top:10px; margin-top:2px; display:flex; flex-direction:column; gap:7px;">
                        <div style="font-size:10px; font-weight:600; color:var(--text3); text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Rental Rates</div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text3);">🎬 Screening</span>
                            <span style="font-weight:700; color:var(--gold);">₱{{ number_format($movie->effective_screening_price) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text3);">📅 Per day</span>
                            <span style="font-weight:700;">₱{{ number_format($movie->price_per_day) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text3);">📆 Per week</span>
                            <span style="font-weight:700;">₱{{ number_format($movie->effective_weekly_price) }}</span>
                        </div>
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

        @if($canRent)
            <div class="card">
                <div class="card-body">
                    <div style="font-size:14px; font-weight:600; margin-bottom:16px; color:var(--text2);">
                        🎬 Rent This Movie
                    </div>
                    <form method="POST" action="{{ route('user.rentals.store') }}">
                        @csrf
                        <input type="hidden" name="movie_id" value="{{ $movie->id }}">

                        {{-- Rental Type selection --}}
                        <div class="form-group">
                            <label class="form-label">Choose Rental Type *</label>
                            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-bottom:4px;">
                                @foreach([
                                    'screening' => ['🎬', 'One-Time\nScreening', $movie->effective_screening_price],
                                    'days'      => ['📅', 'Daily\nRental', $movie->price_per_day],
                                    'weeks'     => ['📆', 'Weekly\nRental', $movie->effective_weekly_price],
                                ] as $type => [$icon, $label, $price])
                                <label style="cursor:pointer;">
                                    <input type="radio" name="rental_type" value="{{ $type }}" style="display:none;"
                                           {{ old('rental_type', 'days') === $type ? 'checked' : '' }}
                                           onchange="updateRentalType()">
                                    <div class="rental-type-card {{ old('rental_type', 'days') === $type ? 'active' : '' }}"
                                         style="padding:10px 6px; border:2px solid var(--border); border-radius:8px; text-align:center; transition:all .15s;">
                                        <div style="font-size:20px; margin-bottom:3px;">{{ $icon }}</div>
                                        <div style="font-size:11px; font-weight:600; margin-bottom:3px; white-space:pre-line; line-height:1.2;">{{ $label }}</div>
                                        <div style="font-size:13px; font-weight:700; color:var(--gold);">₱{{ number_format($price) }}</div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            <div style="font-size:11px; color:var(--text3); margin-top:6px;" id="type-desc">
                                Select your preferred rental type above.
                            </div>
                        </div>

                        {{-- Quantity --}}
                        <div id="qty-group" class="form-group">
                            <label class="form-label" id="qty-label">Number of Days *</label>
                            <input class="form-input" type="number" name="quantity" id="qty-input"
                                   value="{{ old('quantity', 1) }}" min="1" max="8" required oninput="updateTotal()">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Payment Method *</label>
                            <select class="form-select" name="payment_method" required onchange="toggleRef(this.value)">
                                <option value="cash"  {{ old('payment_method')==='cash'  ? 'selected':'' }}>Cash</option>
                                <option value="gcash" {{ old('payment_method')==='gcash' ? 'selected':'' }}>GCash</option>
                                <option value="card"  {{ old('payment_method')==='card'  ? 'selected':'' }}>Credit/Debit Card</option>
                            </select>
                        </div>

                        <div class="form-group" id="ref-group" style="display:none;">
                            <label class="form-label">Reference Number</label>
                            <input class="form-input" type="text" name="payment_reference"
                                   value="{{ old('payment_reference') }}" placeholder="GCash ref / last 4 digits">
                        </div>

                        {{-- Price summary --}}
                        <div style="padding:14px; background:rgba(200,160,74,.07); border:1px solid rgba(200,160,74,.18); border-radius:6px; margin-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
                                <span style="color:var(--text2);">Type:</span>
                                <span id="summary-type">Daily Rental</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
                                <span style="color:var(--text2);">Duration:</span>
                                <span id="summary-duration">1 day</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
                                <span style="color:var(--text2);">Rate:</span>
                                <span id="summary-rate">₱{{ number_format($movie->price_per_day) }}/day</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:20px; font-weight:700; padding-top:8px; border-top:1px solid rgba(200,160,74,.18); margin-top:8px;">
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
                    <div style="font-weight:600; margin-bottom:6px;">All Copies Rented Out</div>
                    <div style="font-size:13px; color:var(--text3); margin-bottom:16px;">All {{ $movie->copies }} {{ Str::plural('copy', $movie->copies) }} of this movie are currently rented. Check back later!</div>
                    <a href="{{ route('user.movies.index') }}" class="btn btn-secondary">Browse Other Movies</a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
const prices = {
    screening: {{ $movie->effective_screening_price }},
    days:      {{ $movie->price_per_day }},
    weeks:     {{ $movie->effective_weekly_price }},
};

const typeDescs = {
    screening: 'Watch the movie once — like a cinema ticket. Access expires after the movie duration.',
    days:      'Keep access for the selected number of days.',
    weeks:     'Best value for longer viewing — discounted weekly rate.',
};

function getRentalType() {
    return document.querySelector('input[name="rental_type"]:checked')?.value || 'days';
}

function updateRentalType() {
    const type = getRentalType();

    // Highlight card
    document.querySelectorAll('.rental-type-card').forEach(c => {
        c.style.borderColor = 'var(--border)';
        c.style.background  = '';
    });
    const radio = document.querySelector(`input[value="${type}"]`);
    if (radio) {
        const card = radio.closest('label').querySelector('.rental-type-card');
        card.style.borderColor = 'var(--gold)';
        card.style.background  = 'rgba(200,160,74,.06)';
    }

    document.getElementById('type-desc').textContent = typeDescs[type];

    const qtyGroup = document.getElementById('qty-group');
    const qtyInput = document.getElementById('qty-input');

    if (type === 'screening') {
        qtyGroup.style.display = 'none';
    } else if (type === 'days') {
        qtyGroup.style.display = '';
        document.getElementById('qty-label').textContent = 'Number of Days *';
        qtyInput.max = 30;
    } else {
        qtyGroup.style.display = '';
        document.getElementById('qty-label').textContent = 'Number of Weeks *';
        qtyInput.max = 8;
    }
    updateTotal();
}

function updateTotal() {
    const type = getRentalType();
    const qty  = parseInt(document.getElementById('qty-input')?.value) || 1;
    let total, rateText, durationText, typeText;

    if (type === 'screening') {
        total        = prices.screening;
        rateText     = '₱' + prices.screening.toLocaleString() + ' (one-time)';
        durationText = 'Single viewing session';
        typeText     = '🎬 One-Time Screening';
    } else if (type === 'weeks') {
        total        = prices.weeks * qty;
        rateText     = '₱' + prices.weeks.toLocaleString() + '/week';
        durationText = qty + ' week' + (qty > 1 ? 's' : '') + ' (' + (qty * 7) + ' days)';
        typeText     = '📆 Weekly Rental';
    } else {
        total        = prices.days * qty;
        rateText     = '₱' + prices.days.toLocaleString() + '/day';
        durationText = qty + ' day' + (qty > 1 ? 's' : '');
        typeText     = '📅 Daily Rental';
    }

    document.getElementById('summary-type').textContent     = typeText;
    document.getElementById('summary-duration').textContent = durationText;
    document.getElementById('summary-rate').textContent     = rateText;
    document.getElementById('total-display').textContent    = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits:2});
}

function toggleRef(method) {
    document.getElementById('ref-group').style.display = (method === 'gcash' || method === 'card') ? '' : 'none';
}

updateRentalType();
</script>
<style>
.rental-type-card:hover { border-color: rgba(200,160,74,.5) !important; background: rgba(200,160,74,.03) !important; cursor: pointer; }
</style>
@endsection