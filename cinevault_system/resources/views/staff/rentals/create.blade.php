@extends('layouts.app')
@section('title', 'New Rental')
@section('page-title', 'Create Rental')

@section('content')
<div style="max-width:640px;">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('staff.rentals.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Movie *</label>
                    <select class="form-select" name="movie_id" required id="movie-select" onchange="updatePrice()">
                        <option value="">— Select a movie —</option>
                        @foreach($movies as $movie)
                            <option value="{{ $movie->id }}"
                                    data-price="{{ $movie->price_per_day }}"
                                    {{ old('movie_id', request('movie_id')) == $movie->id ? 'selected' : '' }}>
                                {{ $movie->poster_icon }} {{ $movie->title }} — ₱{{ number_format($movie->price_per_day) }}/day
                            </option>
                        @endforeach
                    </select>
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
                    <div class="form-group">
                        <label class="form-label">Rental Days *</label>
                        <input class="form-input" type="number" name="days" id="days-input" value="{{ old('days', 1) }}" required min="1" max="30" oninput="updateTotal()">
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
                    <input class="form-input" type="text" name="payment_reference" value="{{ old('payment_reference') }}" placeholder="GCash ref / Card last 4 digits">
                </div>

                {{-- Total Summary --}}
                <div style="padding:14px; background:rgba(200,160,74,.07); border:1px solid rgba(200,160,74,.18); border-radius:6px; margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
                        <span style="color:var(--text2);">Price per day:</span>
                        <span id="ppd-display">₱0</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
                        <span style="color:var(--text2);">Days:</span>
                        <span id="days-display">1</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:700; padding-top:8px; border-top:1px solid rgba(200,160,74,.18); margin-top:8px;">
                        <span style="color:var(--gold);">Total:</span>
                        <span style="color:var(--gold);" id="total-display">₱0</span>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Confirm Rental
                    </button>
                    <a href="{{ route('staff.rentals.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let pricePerDay = 0;

function updatePrice() {
    const sel = document.getElementById('movie-select');
    const opt = sel.options[sel.selectedIndex];
    pricePerDay = parseFloat(opt.dataset.price || 0);
    document.getElementById('ppd-display').textContent = '₱' + pricePerDay.toLocaleString();
    updateTotal();
}

function updateTotal() {
    const days = parseInt(document.getElementById('days-input').value) || 0;
    document.getElementById('days-display').textContent = days;
    document.getElementById('total-display').textContent = '₱' + (pricePerDay * days).toLocaleString();
}

function toggleRef(method) {
    document.getElementById('ref-group').style.display = (method === 'gcash' || method === 'card') ? '' : 'none';
}

updatePrice();
</script>
@endsection