@extends('layouts.app')
@section('title', 'New Rental')
@section('page-title', 'Create Rental')

@section('content')
<div style="max-width:680px;">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.rentals.store') }}" id="rental-form">
                @csrf

                <div class="form-group">
                    <label class="form-label">Movie *</label>
                    <select class="form-select" name="movie_id" required id="movie-select" onchange="updateMovieData()">
                        <option value="">— Select a movie —</option>
                        @foreach($movies as $movie)
                            <option value="{{ $movie->id }}"
                                    data-ppd="{{ $movie->price_per_day }}"
                                    data-pps="{{ $movie->effective_screening_price }}"
                                    data-ppw="{{ $movie->effective_weekly_price }}"
                                    data-avail="{{ $movie->available_copies }}"
                                    data-total="{{ $movie->copies }}"
                                    data-duration="{{ $movie->duration }}"
                                    {{ (old('movie_id', request('movie_id')) == $movie->id) ? 'selected' : '' }}>
                                {{ $movie->title }} — {{ $movie->available_copies }}/{{ $movie->copies }} available
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Copies info --}}
                <div id="copies-info" style="display:none; margin-bottom:14px; padding:10px 14px; background:rgba(200,160,74,.07); border:1px solid rgba(200,160,74,.15); border-radius:6px; font-size:12px;">
                    <span id="copies-text"></span>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div class="form-group">
                        <label class="form-label">Customer Name *</label>
                        <input class="form-input" type="text" name="customer_name" value="{{ old('customer_name') }}" required placeholder="Full name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input class="form-input" type="text" name="customer_contact" value="{{ old('customer_contact') }}" placeholder="09XXXXXXXXX">
                    </div>
                </div>

                {{-- Rental Type --}}
                <div class="form-group">
                    <label class="form-label">Rental Type *</label>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        @foreach([
                            'screening' => ['🎬', 'One-Time Screening', 'Watch once, like a cinema ticket'],
                            'days'      => ['📅', 'Daily Rental', 'Rent for 1–30 days'],
                            'weeks'     => ['📆', 'Weekly Rental', 'Rent for 1–8 weeks (discounted)'],
                        ] as $type => [$icon, $label, $desc])
                        <label style="cursor:pointer;">
                            <input type="radio" name="rental_type" value="{{ $type }}" style="display:none;"
                                   {{ old('rental_type', 'days') === $type ? 'checked' : '' }}
                                   onchange="updateRentalType()">
                            <div class="rental-type-card {{ old('rental_type', 'days') === $type ? 'active' : '' }}"
                                 style="padding:12px; border:2px solid var(--border); border-radius:8px; text-align:center; transition:all .15s;">
                                <div style="font-size:22px; margin-bottom:4px;">{{ $icon }}</div>
                                <div style="font-size:12px; font-weight:600; margin-bottom:2px;">{{ $label }}</div>
                                <div style="font-size:10px; color:var(--text3);">{{ $desc }}</div>
                                <div id="price-{{ $type }}" style="font-size:13px; font-weight:700; color:var(--gold); margin-top:6px;">—</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Quantity (hidden for screening) --}}
                <div id="qty-group" class="form-group">
                    <label class="form-label" id="qty-label">Number of Days *</label>
                    <input class="form-input" type="number" name="quantity" id="qty-input"
                           value="{{ old('quantity', 1) }}" min="1" max="30" oninput="updateTotal()">
                    <div id="qty-hint" style="font-size:11px; color:var(--text3); margin-top:3px;"></div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div class="form-group">
                        <label class="form-label">Payment Method *</label>
                        <select class="form-select" name="payment_method" required onchange="toggleRef(this.value)">
                            <option value="cash"  {{ old('payment_method') === 'cash'  ? 'selected' : '' }}>Cash</option>
                            <option value="gcash" {{ old('payment_method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                            <option value="card"  {{ old('payment_method') === 'card'  ? 'selected' : '' }}>Credit/Debit Card</option>
                        </select>
                    </div>
                    <div class="form-group" id="ref-group" style="display:none;">
                        <label class="form-label">Reference Number</label>
                        <input class="form-input" type="text" name="payment_reference" value="{{ old('payment_reference') }}" placeholder="GCash ref / Card last 4 digits">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-textarea" name="notes" rows="2" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                </div>

                {{-- Total preview --}}
                <div style="padding:14px; background:rgba(200,160,74,.07); border:1px solid rgba(200,160,74,.18); border-radius:6px; margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
                        <span style="color:var(--text2);">Rental type:</span>
                        <span id="type-display">Daily</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
                        <span style="color:var(--text2);">Duration:</span>
                        <span id="duration-display">—</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
                        <span style="color:var(--text2);">Rate:</span>
                        <span id="rate-display">—</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:700; margin-top:8px; padding-top:8px; border-top:1px solid rgba(200,160,74,.18);">
                        <span style="color:var(--gold);">Total:</span>
                        <span style="color:var(--gold);" id="total-display">₱0</span>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Confirm Rental
                    </button>
                    <a href="{{ route('admin.rentals.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let movieData = { ppd: 0, pps: 0, ppw: 0, avail: 0, total: 0 };

function updateMovieData() {
    const sel = document.getElementById('movie-select');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) { document.getElementById('copies-info').style.display = 'none'; return; }

    movieData.ppd   = parseFloat(opt.dataset.ppd  || 0);
    movieData.pps   = parseFloat(opt.dataset.pps  || 0);
    movieData.ppw   = parseFloat(opt.dataset.ppw  || 0);
    movieData.avail = parseInt(opt.dataset.avail  || 0);
    movieData.total = parseInt(opt.dataset.total  || 0);

    const copiesEl = document.getElementById('copies-info');
    copiesEl.style.display = '';
    document.getElementById('copies-text').innerHTML =
        `📦 <strong>${movieData.avail}</strong> of <strong>${movieData.total}</strong> copies available for rent.`;

    // Update price cards
    document.getElementById('price-screening').textContent = '₱' + movieData.pps.toLocaleString();
    document.getElementById('price-days').textContent      = '₱' + movieData.ppd.toLocaleString() + '/day';
    document.getElementById('price-weeks').textContent     = '₱' + movieData.ppw.toLocaleString() + '/week';

    updateTotal();
}

function getRentalType() {
    return document.querySelector('input[name="rental_type"]:checked')?.value || 'days';
}

function updateRentalType() {
    const type = getRentalType();

    // Highlight active card
    document.querySelectorAll('.rental-type-card').forEach(c => {
        c.style.borderColor = 'var(--border)';
        c.style.background  = '';
    });
    const active = document.querySelector(`input[value="${type}"]`).closest('label').querySelector('.rental-type-card');
    active.style.borderColor = 'var(--gold)';
    active.style.background  = 'rgba(200,160,74,.06)';

    const qtyGroup = document.getElementById('qty-group');
    const qtyInput = document.getElementById('qty-input');
    const qtyLabel = document.getElementById('qty-label');
    const qtyHint  = document.getElementById('qty-hint');

    if (type === 'screening') {
        qtyGroup.style.display = 'none';
    } else if (type === 'days') {
        qtyGroup.style.display = '';
        qtyLabel.textContent   = 'Number of Days *';
        qtyInput.max           = 30;
        qtyHint.textContent    = 'Up to 30 days';
    } else {
        qtyGroup.style.display = '';
        qtyLabel.textContent   = 'Number of Weeks *';
        qtyInput.max           = 8;
        qtyHint.textContent    = 'Up to 8 weeks';
    }
    updateTotal();
}

function updateTotal() {
    const type = getRentalType();
    const qty  = parseInt(document.getElementById('qty-input')?.value) || 1;
    let rate, total, durationText, typeText;

    if (type === 'screening') {
        rate         = movieData.pps;
        total        = rate;
        typeText     = '🎬 One-Time Screening';
        durationText = 'Single viewing session';
    } else if (type === 'weeks') {
        rate         = movieData.ppw;
        total        = rate * qty;
        typeText     = '📆 Weekly Rental';
        durationText = qty + ' week' + (qty > 1 ? 's' : '') + ' (' + (qty * 7) + ' days)';
    } else {
        rate         = movieData.ppd;
        total        = rate * qty;
        typeText     = '📅 Daily Rental';
        durationText = qty + ' day' + (qty > 1 ? 's' : '');
    }

    document.getElementById('type-display').textContent     = typeText;
    document.getElementById('duration-display').textContent = durationText;
    document.getElementById('rate-display').textContent     = '₱' + rate.toLocaleString();
    document.getElementById('total-display').textContent    = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits:2});
}

function toggleRef(method) {
    document.getElementById('ref-group').style.display = (method === 'gcash' || method === 'card') ? '' : 'none';
}

// Init
updateMovieData();
updateRentalType();
</script>

<style>
.rental-type-card:hover { border-color: rgba(200,160,74,.5) !important; background: rgba(200,160,74,.03) !important; }
input[name="rental_type"]:checked + .rental-type-card { border-color: var(--gold) !important; background: rgba(200,160,74,.06) !important; }
</style>
@endsection