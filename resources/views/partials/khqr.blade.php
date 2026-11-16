{{-- QR ទូទាត់ KHQR. $amount (ជម្រើស) --}}
<div class="khqr-box text-center">
    <img src="{{ asset(config('bookstore.khqr.image')) }}" alt="KHQR" class="khqr-img">
    <div class="fw-semibold mt-2">{{ config('bookstore.khqr.account') }}</div>
    @isset($amount)
        <div class="mt-1">ចំនួនត្រូវបង់៖ <span class="fs-5 fw-bold text-primary">${{ number_format($amount, 2) }}</span></div>
        <div class="small text-muted">QR នេះមិនមានចំនួនទឹកប្រាក់ទេ សូមបញ្ចូលចំនួនខាងលើដោយខ្លួនឯង ពេលស្កេនក្នុងកម្មវិធីធនាគារ។</div>
    @endisset
</div>
