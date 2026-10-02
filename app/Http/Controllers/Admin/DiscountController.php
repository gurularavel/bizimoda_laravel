<?php

namespace App\Http\Controllers\Admin;

use App\Models\Discount;
use App\Services\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DiscountController extends Controller
{
    public function __construct(protected DiscountService $discounts) {}

    public function index()
    {
        return view('admin.discounts.index', [
            'discounts' => Discount::query()->withCount(['products', 'categories'])->latest()->get(),
        ]);
    }

    public function create(Request $request)
    {
        $discount = new Discount(['type' => 'percent', 'applies_to' => $request->query('scope', 'products'), 'is_active' => true]);

        return $this->form($discount, array_filter([(int) $request->query('category')]), array_filter([(int) $request->query('product')]));
    }

    public function edit(Discount $discount)
    {
        return $this->form($discount, $discount->categories()->pluck('categories.id')->all(), $discount->products()->pluck('products.id')->all());
    }

    protected function form(Discount $discount, array $categoryIds, array $productIds)
    {
        return view('admin.discounts.form', [
            'discount' => $discount,
            'categories' => CategoryController::options(),
            'selectedCategories' => $categoryIds,
            'selectedProducts' => \App\Models\Product::query()->whereIn('id', $productIds)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $discount = new Discount;
        $this->save($request, $discount);

        return redirect()->route('admin.discounts.index')->with('success', 'Endirim yaradıldı və qiymətlər yeniləndi.');
    }

    public function update(Request $request, Discount $discount)
    {
        $this->save($request, $discount);

        return redirect()->route('admin.discounts.index')->with('success', 'Endirim yadda saxlanıldı və qiymətlər yeniləndi.');
    }

    protected function save(Request $request, Discount $discount): void
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'type' => ['required', Rule::in(array_keys(Discount::TYPES))],
            'value' => ['required', 'numeric', 'min:0.01', $request->input('type') === 'percent' ? 'max:100' : 'max:1000000'],
            'applies_to' => ['required', Rule::in(array_keys(Discount::SCOPES))],
            'categories' => 'array|required_if:applies_to,categories',
            'categories.*' => 'integer|exists:categories,id',
            'products' => 'array|required_if:applies_to,products',
            'products.*' => 'integer|exists:products,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ], [
            'categories.required_if' => 'Ən azı bir kateqoriya seçin.',
            'products.required_if' => 'Ən azı bir məhsul seçin.',
            'value.max' => 'Faiz 100-dən çox ola bilməz.',
        ]);

        DB::transaction(function () use ($request, $discount, $data) {
            $discount->fill([
                'name' => $data['name'],
                'type' => $data['type'],
                'value' => $data['value'],
                'applies_to' => $data['applies_to'],
                'starts_at' => $request->filled('starts_at') ? Carbon::parse($data['starts_at']) : null,
                'ends_at' => $request->filled('ends_at') ? Carbon::parse($data['ends_at']) : null,
                'is_active' => $request->boolean('is_active'),
            ])->save();

            $discount->categories()->sync($data['applies_to'] === 'categories' ? ($data['categories'] ?? []) : []);
            $discount->products()->sync($data['applies_to'] === 'products' ? ($data['products'] ?? []) : []);
        });

        $this->discounts->refreshAll();
    }

    public function toggle(Discount $discount)
    {
        $discount->update(['is_active' => ! $discount->is_active]);
        $this->discounts->refreshAll();

        return back()->with('success', $discount->is_active ? 'Endirim aktivləşdirildi.' : 'Endirim dayandırıldı.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        $this->discounts->refreshAll();

        return redirect()->route('admin.discounts.index')->with('success', 'Endirim silindi, qiymətlər bərpa olundu.');
    }
}
