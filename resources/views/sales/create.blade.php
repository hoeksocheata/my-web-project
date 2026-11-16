@extends('layouts.app')
@section('title', 'លក់ថ្មី')
@section('content')
<div class="page-head">
    <div><h1>🛒 បង្កើតការលក់</h1><p>ជ្រើសសៀវភៅ កំណត់ចំនួន ហើយស្តុកនឹងត្រូវកាត់ដោយស្វ័យប្រវត្តិ</p></div>
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> ត្រឡប់ក្រោយ</a>
</div>

@error('items')
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $message }}</div>
@enderror

<form action="{{ route('sales.store') }}" method="POST" id="saleForm" data-once>
    @csrf
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="bk-card">
                <div class="bk-card-header">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="posSearch" class="form-control" placeholder="ស្វែងរកសៀវភៅ..." autocomplete="off">
                    </div>
                </div>
                <div class="pos-list" id="posList"></div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="bk-card" style="position: sticky; top: 80px">
                <div class="bk-card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cart3 me-1"></i> កន្ត្រក</h6>
                    <button type="button" class="btn btn-sm btn-light" id="clearCart">សម្អាត</button>
                </div>
                <div class="bk-card-body">
                    <div id="cart"></div>
                    <div id="cartEmpty" class="text-center text-muted py-4">មិនទាន់មានសៀវភៅក្នុងកន្ត្រក</div>

                    <hr style="border-color: var(--bk-border)">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">សរុប</span>
                        <span class="fs-3 fw-bold text-primary" id="grand">$0.00</span>
                    </div>
                    <label class="form-label" for="customer_name">ឈ្មោះអតិថិជន (ស្រេចចិត្ត)</label>
                    <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" maxlength="100" class="form-control mb-3">
                    <button type="button" class="btn btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#khqrModal"><i class="bi bi-qr-code me-1"></i> បង្ហាញ QR ទូទាត់ (KHQR)</button>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" id="checkout" disabled>
                        <i class="bi bi-check2-circle me-1"></i> បញ្ជាក់ការលក់
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="modal fade" id="khqrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow"><div class="modal-body p-4">
            <div class="text-center mb-2 text-muted small">ឱ្យអតិថិជនស្កេនដើម្បីទូទាត់</div>
            @include('partials.khqr')
            <div class="text-center mt-3"><span class="text-muted">ចំនួនត្រូវបង់</span><div class="fs-3 fw-bold text-primary" id="qrAmount">$0.00</div></div>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var books = @json($books);
    var cart = {};                     // book_id -> quantity
    var $list = document.getElementById('posList');
    var $cart = document.getElementById('cart');
    var money = function (n) { return '$' + n.toFixed(2); };
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; };
    var byId = {}; books.forEach(function (b) { byId[b.id] = b; });

    function renderList() {
        var q = document.getElementById('posSearch').value.trim().toLowerCase();
        var rows = books.filter(function (b) { return !q || (b.title + ' ' + b.author).toLowerCase().indexOf(q) > -1; });
        if (!rows.length) { $list.innerHTML = '<div class="empty py-4">រកមិនឃើញសៀវភៅ (ឬអស់ស្តុក)</div>'; return; }
        $list.innerHTML = rows.map(function (b) {
            var left = b.stock - (cart[b.id] || 0);
            return '<div class="pos-item">' +
                '<div class="flex-grow-1"><div class="fw-semibold">' + esc(b.title) + '</div>' +
                '<small class="text-muted">' + esc(b.author) + ' · ស្តុក ' + b.stock + '</small></div>' +
                '<div class="fw-bold">' + money(b.price) + '</div>' +
                '<button type="button" class="btn btn-soft btn-icon" data-add="' + b.id + '"' + (left <= 0 ? ' disabled' : '') + '><i class="bi bi-plus-lg"></i></button></div>';
        }).join('');
    }

    function renderCart() {
        var ids = Object.keys(cart), total = 0, html = '';
        ids.forEach(function (id, i) {
            var b = byId[id], q = cart[id]; total += b.price * q;
            html += '<div class="d-flex align-items-center gap-2 mb-3">' +
                '<div class="flex-grow-1"><div class="fw-semibold">' + esc(b.title) + '</div><small class="text-muted">' + money(b.price) + ' × ' + q + '</small></div>' +
                '<div class="qty-box"><button type="button" data-dec="' + id + '">−</button>' +
                '<input type="number" min="1" max="' + b.stock + '" value="' + q + '" data-qty="' + id + '">' +
                '<button type="button" data-inc="' + id + '">+</button></div>' +
                '<div class="fw-bold text-end" style="min-width:64px">' + money(b.price * q) + '</div>' +
                '<button type="button" class="btn btn-sm text-danger p-1" data-del="' + id + '"><i class="bi bi-x-lg"></i></button>' +
                '<input type="hidden" name="items[' + i + '][book_id]" value="' + id + '">' +
                '<input type="hidden" name="items[' + i + '][quantity]" value="' + q + '"></div>';
        });
        $cart.innerHTML = html;
        document.getElementById('cartEmpty').classList.toggle('d-none', ids.length > 0);
        document.getElementById('grand').textContent = money(total);
        document.getElementById('qrAmount').textContent = money(total);
        document.getElementById('checkout').disabled = ids.length === 0;
        renderList();
    }

    function setQty(id, q) {
        var max = byId[id].stock;
        q = Math.max(0, Math.min(max, parseInt(q, 10) || 0));
        if (q === 0) delete cart[id]; else cart[id] = q;
        renderCart();
    }

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-add],[data-inc],[data-dec],[data-del]'); if (!t) return;
        if (t.dataset.add) setQty(t.dataset.add, (cart[t.dataset.add] || 0) + 1);
        if (t.dataset.inc) setQty(t.dataset.inc, (cart[t.dataset.inc] || 0) + 1);
        if (t.dataset.dec) setQty(t.dataset.dec, (cart[t.dataset.dec] || 0) - 1);
        if (t.dataset.del) setQty(t.dataset.del, 0);
    });
    document.addEventListener('change', function (e) { if (e.target.dataset && e.target.dataset.qty) setQty(e.target.dataset.qty, e.target.value); });
    document.getElementById('posSearch').addEventListener('input', renderList);
    document.getElementById('clearCart').addEventListener('click', function () { cart = {}; renderCart(); });

    renderList(); renderCart();
})();
</script>
@endpush
