<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileSignatureVerifier;
use App\Services\ProductAdminService;
use App\Services\PublicProductImageStorage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Ported from app/admin/(protected)/products/{actions.ts,page.tsx} and
 * components/admin/{ProductAdminTable,ProductEditModal}.tsx. The source
 * edits products in a client-side modal over a fetch-based image upload;
 * this is full-page create/edit forms instead (see
 * resources/views/admin/products/_form.blade.php) — same fields, same
 * validation, same two-step image upload (upload first, submit the
 * returned URL with the rest of the form), just without a SPA layer.
 */
class ProductController extends Controller
{
    private const array CATEGORY_LABELS = [
        'pumps' => 'Насосы',
        'valves' => 'Трубопроводная арматура',
        'water' => 'Оборудование для водоснабжения',
        'control' => 'Электрические шкафы управления',
    ];

    public function index(Request $request, ProductAdminService $products): View
    {
        $query = trim((string) $request->query('q', ''));
        $all = $products->listForAdmin();

        $filtered = $query === ''
            ? $all
            : $all->filter(function ($product) use ($query) {
                $needle = mb_strtolower($query);

                return str_contains(mb_strtolower($product->ru_name), $needle)
                    || str_contains(mb_strtolower($product->code), $needle);
            });

        return view('admin.products.index', [
            'products' => $filtered->values(),
            'query' => $query,
            'categoryLabels' => self::CATEGORY_LABELS,
        ]);
    }

    public function create(ProductAdminService $products): View
    {
        return view('admin.products.form', [
            'mode' => 'create',
            'product' => null,
            'sections' => $products->sectionOptions(),
            'categoryLabels' => self::CATEGORY_LABELS,
            'locales' => ProductAdminService::LOCALES,
        ]);
    }

    public function store(Request $request, ProductAdminService $products): RedirectResponse
    {
        $result = $products->create($this->inputFromRequest($request, includeSlug: true));

        if (isset($result['errors'])) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('admin.products.index')->with('admin_success', 'Товар создан.');
    }

    public function edit(Request $request, ProductAdminService $products): View
    {
        $slug = $this->routeParam($request, 'slug');
        $product = $products->getForAdmin($slug);
        abort_if($product === null, 404);

        return view('admin.products.form', [
            'mode' => 'edit',
            'product' => $product,
            'sections' => $products->sectionOptions(),
            'categoryLabels' => self::CATEGORY_LABELS,
            'locales' => ProductAdminService::LOCALES,
        ]);
    }

    public function update(Request $request, ProductAdminService $products): RedirectResponse
    {
        $slug = $this->routeParam($request, 'slug');
        $result = $products->update($slug, $this->inputFromRequest($request, includeSlug: false));

        if (isset($result['errors'])) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('admin.products.index')->with('admin_success', 'Товар сохранён.');
    }

    public function destroy(Request $request, ProductAdminService $products): RedirectResponse
    {
        $products->delete($this->routeParam($request, 'slug'));

        return redirect()->route('admin.products.index')->with('admin_success', 'Товар удалён.');
    }

    /**
     * Ported from app/api/admin/products/image/route.ts. Separate limits
     * and disk from Stage 6's contact attachments — see
     * PublicProductImageStorage's doc block for why they aren't shared.
     */
    public function uploadImage(Request $request, FileSignatureVerifier $signatures, PublicProductImageStorage $storage): JsonResponse
    {
        $allowedTypes = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/webp' => '.webp'];
        $maxSizeBytes = 8 * 1024 * 1024;

        $file = $request->file('file');
        $slug = preg_replace('/[^a-z0-9-]/i', '', (string) $request->input('slug', 'unassigned')) ?: 'unassigned';

        if (! $file) {
            return response()->json(['error' => 'No file provided'], 400);
        }

        $extension = $allowedTypes[$file->getMimeType()] ?? null;
        if ($extension === null) {
            return response()->json(['error' => 'Unsupported file type'], 400);
        }
        if ($file->getSize() > $maxSizeBytes) {
            return response()->json(['error' => 'File too large'], 400);
        }

        $contents = (string) file_get_contents($file->getRealPath());
        if (! $signatures->matches($contents, $extension)) {
            return response()->json(['error' => "File content doesn't match its declared type"], 400);
        }

        $saved = $storage->save($contents, mb_strtolower($slug), $extension);

        return response()->json(['url' => $saved['url']]);
    }

    /** @return array{slug?:string,code:string,category:string,section_id:string,image:string,stock_quantity:int,price:int,translations:array<string,array{name:string,description:array,specs:array,applications:array}>} */
    private function inputFromRequest(Request $request, bool $includeSlug): array
    {
        $translations = [];
        foreach (ProductAdminService::LOCALES as $locale) {
            $translations[$locale] = [
                'name' => (string) $request->input("translations.{$locale}.name", ''),
                'description' => $this->linesToArray((string) $request->input("translations.{$locale}.description", '')),
                'specs' => $this->linesToArray((string) $request->input("translations.{$locale}.specs", '')),
                'applications' => $this->linesToArray((string) $request->input("translations.{$locale}.applications", '')),
            ];
        }

        $input = [
            'code' => (string) $request->input('code', ''),
            'category' => (string) $request->input('category', 'pumps'),
            'section_id' => (string) $request->input('section_id', ''),
            'image' => (string) $request->input('image', ''),
            'stock_quantity' => (int) $request->input('stock_quantity', 0),
            'price' => (int) $request->input('price', 0),
            'translations' => $translations,
        ];

        if ($includeSlug) {
            $input['slug'] = mb_strtolower(trim((string) $request->input('slug', '')));
        }

        return $input;
    }

    /** Textarea → array of non-empty trimmed lines, one entry per line — mirrors the source's split("\n").map(trim).filter(Boolean). */
    private function linesToArray(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->values()
            ->all();
    }
}
