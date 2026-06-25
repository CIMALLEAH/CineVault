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

    document.getElementById('copies-info').style.display = '';
    document.getElementById('copies-text').innerHTML =
        `📦 <strong>${movieData.avail}</strong> of <strong>${movieData.total}</strong> copies available for rent.`;

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

    const qtyGroup = document.getElementById('qty-group');
    const qtyInput = document.getElementById('qty-input');

    if (type === 'screening') {
        qtyGroup.style.display = 'none';
    } else if (type === 'days') {
        qtyGroup.style.display = '';
        document.getElementById('qty-label').textContent = 'Number of Days *';
        qtyInput.max = 30;
        document.getElementById('qty-hint').textContent = 'Up to 30 days';
    } else {
        qtyGroup.style.display = '';
        document.getElementById('qty-label').textContent = 'Number of Weeks *';
        qtyInput.max = 8;
        document.getElementById('qty-hint').textContent = 'Up to 8 weeks';
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

updateMovieData();
updateRentalType();
</script>
<style>
.rental-type-card:hover { border-color: rgba(200,160,74,.5) !important; background: rgba(200,160,74,.03) !important; cursor:pointer; }
</style>