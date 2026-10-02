<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DiscountService
{
    /** @var Collection<int, array{discount: Discount, products: array, categories: array}>|null */
    protected ?Collection $running = null;

    protected array $productCategories = [];

    public function running(): Collection
    {
        if ($this->running !== null) {
            return $this->running;
        }

        return $this->running = Discount::query()->running()->with(['products:id', 'categories:id'])->get()
            ->map(function (Discount $d) {
                $categoryIds = [];
                foreach ($d->categories as $category) {
                    // Kateqoriya endirimi alt kateqoriyalara da aiddir
                    $categoryIds = array_merge($categoryIds, Category::descendantsAndSelf($category->id)->pluck('id')->all());
                }

                return [
                    'discount' => $d,
                    'products' => array_flip($d->products->pluck('id')->all()),
                    'categories' => array_flip($categoryIds),
                ];
            });
    }

    /**
     * Məhsula tətbiq olunan ən sərfəli endirim.
     *
     * @return array{amount: float, discount: ?Discount}
     */
    public function best(Product $product, float $price): array
    {
        $best = ['amount' => 0.0, 'discount' => null];
        if ($price <= 0 || ! $product->exists) {
            return $best;
        }

        foreach ($this->running() as $row) {
            if (! $this->applies($row, $product)) {
                continue;
            }
            $amount = $row['discount']->amountFor($price);
            if ($amount > $best['amount']) {
                $best = ['amount' => $amount, 'discount' => $row['discount']];
            }
        }

        return $best;
    }

    protected function applies(array $row, Product $product): bool
    {
        return match ($row['discount']->applies_to) {
            'all' => true,
            'products' => isset($row['products'][$product->id]),
            'categories' => (bool) array_intersect_key($row['categories'], array_flip($this->categoryIds($product))),
            default => false,
        };
    }

    protected function categoryIds(Product $product): array
    {
        return $this->productCategories[$product->id] ??= DB::table('category_product')
            ->where('product_id', $product->id)->pluck('category_id')
            ->push($product->main_category_id)->unique()->all();
    }

    /** Endirim dəyişdikdə (və ya vaxtı başlayanda/bitəndə) siyahı qiymətlərini yenidən hesablayır */
    public function refreshAll(): int
    {
        $this->flush();
        $pricing = app(SetPricingService::class);
        $count = 0;

        Product::withTrashed()->with('setItems.component')->chunkById(200, function ($products) use ($pricing, &$count) {
            foreach ($products as $product) {
                $pricing->refreshComputed($product);
                $count++;
            }
        });

        return $count;
    }

    public function flush(): void
    {
        $this->running = null;
        $this->productCategories = [];
    }
}
