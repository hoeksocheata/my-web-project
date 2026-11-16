{{-- $book (nullable), $categories, $action, $method --}}
@php $book = $book ?? null; @endphp
<form action="{{ $action }}" method="POST" enctype="multipart/form-data" data-once novalidate>
    @csrf
    @if(($method ?? 'POST') !== 'POST') @method($method) @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="mb-3">
                <label class="form-label" for="title">ចំណងជើងសៀវភៅ <span class="text-danger">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $book?->title) }}"
                       class="form-control @error('title') is-invalid @enderror" required autofocus>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="author">អ្នកនិពន្ធ <span class="text-danger">*</span></label>
                    <input type="text" id="author" name="author" value="{{ old('author', $book?->author) }}"
                           class="form-control @error('author') is-invalid @enderror" required>
                    @error('author') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="isbn">ISBN / បាកូដ</label>
                    <input type="text" id="isbn" name="isbn" value="{{ old('isbn', $book?->isbn) }}" maxlength="20"
                           class="form-control @error('isbn') is-invalid @enderror" placeholder="978-...">
                    @error('isbn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="category_id">ប្រភេទ</label>
                    <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">— គ្មានប្រភេទ —</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected((string) old('category_id', $book?->category_id) === (string) $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($categories->isEmpty())
                        <div class="form-text">មិនទាន់មានប្រភេទ — <a href="{{ route('categories.index') }}">បង្កើតប្រភេទ</a></div>
                    @endif
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="price">តម្លៃ ($) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $book?->price) }}"
                               class="form-control @error('price') is-invalid @enderror" required>
                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="stock">ចំនួនក្នុងស្តុក <span class="text-danger">*</span></label>
                    <input type="number" min="0" step="1" id="stock" name="stock" value="{{ old('stock', $book?->stock ?? 0) }}"
                           class="form-control @error('stock') is-invalid @enderror" required>
                    @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label" for="description">ការពិពណ៌នា</label>
                <textarea id="description" name="description" rows="5" maxlength="2000"
                          class="form-control @error('description') is-invalid @enderror" placeholder="សង្ខេបអំពីសៀវភៅ...">{{ old('description', $book?->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="col-lg-5">
            <label class="form-label">រូបគម្រប</label>
            <div class="upload-box" data-upload>
                <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="d-none">
                <img data-preview class="img-fluid {{ $book?->image_url ? '' : 'd-none' }}" src="{{ $book?->image_url }}" alt="">
                <div data-hint class="{{ $book?->image_url ? 'd-none' : '' }} py-4">
                    <i class="bi bi-cloud-arrow-up fs-1 text-muted"></i>
                    <div class="fw-semibold">ចុច ឬអូសរូបភាពមកទីនេះ</div>
                    <small class="text-muted">JPG, PNG, WEBP · អតិបរមា 2MB</small>
                </div>
            </div>
            @error('image') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

            @if($book?->image)
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                    <label class="form-check-label small" for="remove_image">លុបរូបភាពបច្ចុប្បន្ន</label>
                </div>
            @endif
        </div>
    </div>

    <hr class="my-4" style="border-color: var(--bk-border)">
    <div class="d-flex gap-2 justify-content-end">
        <a href="{{ route('books.index') }}" class="btn btn-light px-4">បោះបង់</a>
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2-circle me-1"></i> រក្សាទុក</button>
    </div>
</form>
