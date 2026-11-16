<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookController extends Controller
{
    /** តម្រៀបដែលអនុញ្ញាត: key => [column, direction] */
    private const SORTS = [
        'latest'     => ['created_at', 'desc'],
        'oldest'     => ['created_at', 'asc'],
        'title'      => ['title', 'asc'],
        'price_asc'  => ['price', 'asc'],
        'price_desc' => ['price', 'desc'],
        'stock_asc'  => ['stock', 'asc'],
    ];

    public function index(Request $request)
    {
        $search   = trim((string) $request->input('search'));
        $category = $request->input('category');
        $stock    = $request->input('stock');
        $sort     = array_key_exists($request->input('sort'), self::SORTS) ? $request->input('sort') : 'latest';
        $view     = $request->input('view') === 'grid' ? 'grid' : 'table';

        [$column, $direction] = self::SORTS[$sort];

        $books = Book::with('category')
            ->withAvg('reviews', 'rating')->withCount('reviews')
            ->search($search)
            ->when($category, fn ($q, $c) => $q->where('category_id', $c))
            ->stockStatus($stock)
            ->orderBy($column, $direction)
            ->orderBy('id', 'desc')
            ->paginate($view === 'grid' ? 12 : 8)
            ->withQueryString();

        $stats = [
            'books'      => Book::count(),
            'copies'     => (int) Book::sum('stock'),
            'value'      => (float) Book::selectRaw('COALESCE(SUM(price * stock), 0) as v')->value('v'),
            'low'        => Book::stockStatus('low')->count(),
            'out'        => Book::stockStatus('out')->count(),
            'categories' => Category::count(),
        ];

        $categories = Category::orderBy('name')->get();
        $wishIds    = $this->wishIds($request);
        $lowBooks   = $request->user()->isAdmin()
            ? Book::where('stock', '<=', Book::LOW_STOCK)->orderBy('stock')->orderBy('title')->limit(5)->get()
            : collect();

        return view('books.index', compact('books', 'stats', 'categories', 'search', 'category', 'stock', 'sort', 'view', 'wishIds', 'lowBooks'));
    }

    public function show(Request $request, Book $book)
    {
        $book->load('category', 'reviews.user')->loadAvg('reviews', 'rating');
        $wishIds   = $this->wishIds($request);
        $user      = $request->user();
        $isAdmin   = $user->isAdmin();
        $movements = $isAdmin ? $book->movements()->with('user')->limit(15)->get() : collect();
        $canReview = ! $isAdmin && $user->hasPurchased($book);
        $myReview  = $canReview ? $book->reviews->firstWhere('user_id', $user->id) : null;

        return view('books.show', compact('book', 'wishIds', 'movements', 'canReview', 'myReview'));
    }

    public function create()
    {
        return view('books.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request, StockService $stock)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('books', 'public');
        }

        $initial = (int) $data['stock'];
        $data['stock'] = 0;

        DB::transaction(function () use ($data, $initial, $stock, $request) {
            $book = Book::create($data);
            if ($initial > 0) {
                $stock->change($book, $initial, 'initial', $request->user()->id, 'ស្តុកដំបូង');
            }
        });

        return redirect()->route('books.index')->with('success', 'បានបន្ថែមសៀវភៅដោយជោគជ័យ!');
    }

    public function edit(Book $book)
    {
        return view('books.edit', [
            'book'       => $book,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book, StockService $stock)
    {
        $data = $this->validated($request, $book);

        if ($request->boolean('remove_image') && $book->image && ! $request->hasFile('image')) {
            Storage::disk('public')->delete($book->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($book->image) {
                Storage::disk('public')->delete($book->image);
            }
            $data['image'] = $request->file('image')->store('books', 'public');
        }

        $newStock = (int) $data['stock'];
        unset($data['stock']);

        DB::transaction(function () use ($book, $data, $newStock, $stock, $request) {
            $locked = Book::whereKey($book->id)->lockForUpdate()->firstOrFail();
            $locked->update($data);

            // កត់ត្រាប្រវត្តិ ប្រសិនបើស្តុកត្រូវបានកែដោយដៃ
            if ($newStock !== $locked->stock) {
                $stock->change($locked, $newStock - $locked->stock, 'adjust', $request->user()->id, 'កែតម្រូវដោយដៃ');
            }
        });

        return redirect()->route('books.index')->with('success', 'បានកែប្រែទិន្នន័យជោគជ័យ!');
    }

    public function destroy(Book $book)
    {
        if ($book->image) {
            Storage::disk('public')->delete($book->image);
        }

        $book->delete();

        return redirect()->route('books.index')->with('success', 'បានលុបសៀវភៅរួចរាល់!');
    }

    /** នាំចេញបញ្ជីសៀវភៅជា CSV (បើកក្នុង Excel បានត្រឹមត្រូវជាមួយអក្សរខ្មែរ) */
    public function export(Request $request): StreamedResponse
    {
        $books = Book::with('category')
            ->search(trim((string) $request->input('search')))
            ->when($request->input('category'), fn ($q, $c) => $q->where('category_id', $c))
            ->stockStatus($request->input('stock'))
            ->orderBy('id');

        $filename = 'books-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($books) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, ['ID', 'ចំណងជើង', 'អ្នកនិពន្ធ', 'ISBN', 'ប្រភេទ', 'តម្លៃ ($)', 'ស្តុក', 'តម្លៃសរុប ($)']);
            $books->chunk(200, function ($chunk) use ($out) {
                foreach ($chunk as $b) {
                    fputcsv($out, [
                        $b->id, $b->title, $b->author, $b->isbn, $b->category?->name,
                        number_format((float) $b->price, 2, '.', ''),
                        $b->stock,
                        number_format((float) $b->price * $b->stock, 2, '.', ''),
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** ID សៀវភៅក្នុងបញ្ជីចូលចិត្តរបស់អតិថិជន (Admin មិនមាន) */
    private function wishIds(Request $request): array
    {
        $user = $request->user();

        return ($user && ! $user->isAdmin()) ? $user->wishlist()->pluck('books.id')->all() : [];
    }

    private function validated(Request $request, ?Book $book = null): array
    {
        return $request->validate([
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'isbn'        => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book?->id)],
            'category_id' => 'nullable|exists:categories,id',
            'price'       => 'required|numeric|min:0|max:999999.99',
            'stock'       => 'required|integer|min:0|max:100000',
            'description' => 'nullable|string|max:2000',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'title.required'  => 'សូមបញ្ចូលចំណងជើងសៀវភៅ',
            'author.required' => 'សូមបញ្ចូលឈ្មោះអ្នកនិពន្ធ',
            'isbn.unique'     => 'ISBN នេះមានរួចហើយ',
            'price.required'  => 'សូមបញ្ចូលតម្លៃ',
            'price.numeric'   => 'តម្លៃត្រូវតែជាលេខ',
            'stock.required'  => 'សូមបញ្ចូលចំនួនស្តុក',
            'stock.integer'   => 'ស្តុកត្រូវតែជាចំនួនគត់',
            'image.image'     => 'ឯកសារត្រូវតែជារូបភាព',
            'image.max'       => 'រូបភាពមានទំហំធំជាង 2MB',
        ]);
    }

    // ---------- នាំចូល CSV ----------

    public function importForm()
    {
        return view('books.import');
    }

    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['title', 'author', 'isbn', 'category', 'price', 'stock', 'description']);
            fputcsv($out, ['សៀវភៅគំរូ', 'អ្នកនិពន្ធគំរូ', '9780000000001', 'អប់រំ', '12.50', '10', 'ការពិពណ៌នាខ្លី']);
            fclose($out);
        }, 'books-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** នាំចូលសៀវភៅច្រើនក្បាលពី CSV (អតិបរមា 500 ជួរ). មាន ISBN ដូចគ្នា ឬចំណងជើង+អ្នកនិពន្ធដូចគ្នា → កែប្រែ */
    public function import(Request $request, StockService $stock)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048'], [
            'file.required' => 'សូមជ្រើសរើសឯកសារ CSV',
            'file.mimes'    => 'ឯកសារត្រូវតែជា .csv',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $first  = fgets($handle);
        rewind($handle);
        if (str_starts_with((string) $first, "\xEF\xBB\xBF")) {
            fread($handle, 3); // រំលង BOM
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), fgetcsv($handle) ?: []);
        foreach (['title', 'author', 'price'] as $need) {
            if (! in_array($need, $header, true)) {
                fclose($handle);
                return back()->withErrors(['file' => "ឯកសារត្រូវមានជួរឈរ «{$need}» (ទាញយកគំរូខាងក្រោម)"]);
            }
        }

        $created = $updated = 0;
        $errors  = [];
        $line    = 1;

        DB::transaction(function () use ($handle, $header, $stock, $request, &$created, &$updated, &$errors, &$line) {
            while (($cells = fgetcsv($handle)) !== false && $line <= 500) {
                $line++;
                if (count(array_filter($cells, fn ($c) => trim((string) $c) !== '')) === 0) {
                    continue; // ជួរទទេ
                }
                $row = array_combine($header, array_pad(array_slice($cells, 0, count($header)), count($header), null));
                $row = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row);
                $row['stock'] = ($row['stock'] ?? '') === '' ? 0 : $row['stock'];

                $v = Validator::make($row, [
                    'title'       => 'required|string|max:255',
                    'author'      => 'required|string|max:255',
                    'isbn'        => 'nullable|string|max:20',
                    'price'       => 'required|numeric|min:0|max:999999.99',
                    'stock'       => 'required|integer|min:0|max:100000',
                    'description' => 'nullable|string|max:2000',
                ]);
                if ($v->fails()) {
                    $errors[] = "ជួរ {$line}: ".$v->errors()->first();
                    continue;
                }

                $categoryId = ! empty($row['category'])
                    ? Category::firstOrCreate(['name' => $row['category']])->id : null;

                $book = ! empty($row['isbn']) ? Book::where('isbn', $row['isbn'])->first() : null;
                $book ??= Book::where('title', $row['title'])->where('author', $row['author'])->first();

                $fields = [
                    'title' => $row['title'], 'author' => $row['author'], 'isbn' => $row['isbn'] ?: null,
                    'category_id' => $categoryId, 'price' => $row['price'], 'description' => $row['description'] ?? null,
                ];

                if ($book) {
                    $book->update($fields);
                    $diff = (int) $row['stock'] - $book->stock;
                    if ($diff !== 0) {
                        $stock->change($book, $diff, 'adjust', $request->user()->id, 'នាំចូល CSV');
                    }
                    $updated++;
                } else {
                    $book = Book::create($fields + ['stock' => 0]);
                    if ((int) $row['stock'] > 0) {
                        $stock->change($book, (int) $row['stock'], 'initial', $request->user()->id, 'នាំចូល CSV');
                    }
                    $created++;
                }
            }
        });
        fclose($handle);

        return redirect()->route('books.import')->with('import_result', compact('created', 'updated', 'errors'));
    }
}
