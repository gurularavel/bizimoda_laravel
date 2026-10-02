<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSetItem;
use Illuminate\Validation\ValidationException;

/**
 * Məhsul qiymətinin YEGANƏ mənbəyi (məhsul səhifəsi, səbət, sifariş, siyahı).
 *
 * Dəst (set): qiymət = Σ(modul qiyməti × seçilmiş say) + opsiyon əlavələri.
 * Sadə/modul: qiymət = məhsul qiyməti + opsiyon əlavələri.
 *
 * Konfiqurasiya formatı:
 *   ['components' => [component_id => qty, ...], 'options' => [option_id => option_value_id, ...]]
 */
class SetPricingService
{
    public function __construct(protected DiscountService $discounts) {}

    /** Standart konfiqurasiya: modulların default sayı + məcburi opsiyonların default dəyəri */
    public function defaultConfiguration(Product $product): array
    {
        $config = ['components' => [], 'options' => []];

        if ($product->isSet()) {
            foreach ($this->setItems($product) as $item) {
                $config['components'][$item->component_id] = (int) $item->default_qty;
            }
        }

        foreach ($product->productOptions()->with('values')->get() as $productOption) {
            $default = $productOption->values->firstWhere('is_default', true)
                ?? ($productOption->is_required ? $productOption->values->first() : null);
            if ($default) {
                $config['options'][$productOption->option_id] = $default->option_value_id;
            }
        }

        return $config;
    }

    /**
     * Müştərinin göndərdiyi konfiqurasiyanı normallaşdırır və yoxlayır.
     *
     * @throws ValidationException
     */
    public function validate(Product $product, array $input): array
    {
        $errors = [];
        $config = ['components' => [], 'options' => []];
        $locale = app()->getLocale();

        if ($product->isSet()) {
            $requested = (array) ($input['components'] ?? []);
            $sum = 0;

            foreach ($this->setItems($product) as $item) {
                $name = $item->component->getTranslation('name', $locale);
                $raw = $requested[$item->component_id] ?? $item->default_qty;
                $qty = filter_var($raw, FILTER_VALIDATE_INT);

                if ($qty === false || $qty < 0) {
                    $errors["components.{$item->component_id}"] = __(':name: say düzgün deyil.', ['name' => $name]);
                    continue;
                }
                if ($qty === 0 && $item->is_required) {
                    $errors["components.{$item->component_id}"] = __(':name dəstin məcburi hissəsidir.', ['name' => $name]);
                    continue;
                }
                if ($qty > 0 && $qty < $item->min_qty) {
                    $errors["components.{$item->component_id}"] = __(':name üçün minimum say: :min.', ['name' => $name, 'min' => $item->min_qty]);
                    continue;
                }
                if ($item->max_qty && $qty > $item->max_qty) {
                    $errors["components.{$item->component_id}"] = __(':name üçün maksimum say: :max.', ['name' => $name, 'max' => $item->max_qty]);
                    continue;
                }

                $config['components'][$item->component_id] = $qty;
                $sum += $qty;
            }

            if (! $errors && $sum === 0) {
                $errors['components'] = __('Zəhmət olmasa əlavə etmək istədiyiniz məhsulları seçin.');
            }
        }

        $requestedOptions = (array) ($input['options'] ?? []);
        foreach ($product->productOptions()->with(['values', 'option'])->get() as $productOption) {
            $valueId = $requestedOptions[$productOption->option_id] ?? null;

            if ($valueId === null || $valueId === '') {
                if ($productOption->is_required) {
                    $errors["options.{$productOption->option_id}"] = __(':name seçilməlidir!', ['name' => $productOption->option->getTranslation('name', $locale)]);
                }
                continue;
            }

            if (! $productOption->values->contains('option_value_id', (int) $valueId)) {
                $errors["options.{$productOption->option_id}"] = __('Seçim düzgün deyil.');
                continue;
            }

            $config['options'][$productOption->option_id] = (int) $valueId;
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $config;
    }

    /**
     * Konfiqurasiya edilmiş bir ədəd məhsulun qiyməti.
     *
     * @return array{unit_price: float, unit_old_price: ?float, components: array, options: array}
     */
    public function price(Product $product, array $config): array
    {
        $components = [];
        $base = 0.0;
        $baseOld = 0.0;

        if ($product->isSet()) {
            foreach ($this->setItems($product) as $item) {
                $qty = (int) ($config['components'][$item->component_id] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $unit = $item->unitPrice();
                $unitOld = $item->unitOldPrice();
                $components[] = [
                    'item' => $item,
                    'product' => $item->component,
                    'qty' => $qty,
                    'unit_price' => $unit,
                    'unit_old_price' => $unitOld,
                    'total' => round($unit * $qty, 2),
                ];
                $base += $unit * $qty;
                $baseOld += ($unitOld ?? $unit) * $qty;
            }
        } else {
            $base = (float) $product->price;
            $baseOld = $product->old_price && (float) $product->old_price > $base ? (float) $product->old_price : $base;
        }

        $options = [];
        $modifier = 0.0;
        $modifierOld = 0.0;
        if (! empty($config['options'])) {
            $values = $product->optionValues()->with(['optionValue', 'productOption.option'])
                ->whereIn('option_value_id', array_values($config['options']))->get();

            foreach ($config['options'] as $optionId => $valueId) {
                $pov = $values->first(fn ($v) => $v->option_value_id == $valueId && $v->productOption->option_id == $optionId);
                if (! $pov) {
                    continue;
                }
                $mod = $pov->modifierFor($base);
                $modifier += $mod;
                $modifierOld += $pov->modifierFor($baseOld);
                $options[] = [
                    'option_id' => (int) $optionId,
                    'option_value_id' => (int) $valueId,
                    'name' => $pov->productOption->option->getTranslations('name'),
                    'value' => $pov->optionValue->getTranslations('name'),
                    'modifier' => $mod,
                ];
            }
        }

        $unitPrice = round($base + $modifier, 2);
        $unitOld = round($baseOld + $modifierOld, 2);

        // Endirim kampaniyası (məhsul / kateqoriya / hamısı) — ən sərfəlisi yekun qiymətə tətbiq olunur
        $discount = $this->discounts->best($product, $unitPrice);
        if ($discount['amount'] > 0) {
            $unitOld = max($unitOld, $unitPrice);
            $unitPrice = round($unitPrice - $discount['amount'], 2);
        }

        return [
            'unit_price' => $unitPrice,
            'unit_old_price' => $unitOld > $unitPrice ? $unitOld : null,
            'discount' => $discount['amount'],
            'discount_name' => $discount['discount']?->name,
            'components' => $components,
            'options' => $options,
        ];
    }

    /**
     * Siyahı/filtr/sıralama üçün computed_price sütununu standart konfiqurasiyaya
     * (dəstdə modulların standart sayı) və aktiv endirimlərə görə yeniləyir.
     */
    public function refreshComputed(Product $product): void
    {
        $product->unsetRelation('setItems');
        $price = $this->price($product, ['components' => $this->defaultConfiguration($product)['components'], 'options' => []]);

        Product::withTrashed()->whereKey($product->getKey())->update([
            'computed_price' => $price['unit_price'],
            'computed_old_price' => $price['unit_old_price'],
        ]);
        $product->computed_price = $price['unit_price'];
        $product->computed_old_price = $price['unit_old_price'];
    }

    public function refreshSetsContaining(Product $component): void
    {
        $setIds = ProductSetItem::query()->where('component_id', $component->getKey())->pluck('set_id');
        Product::query()->whereIn('id', $setIds)->get()->each(fn (Product $set) => $this->refreshComputed($set));
    }

    protected function setItems(Product $product)
    {
        if (! $product->relationLoaded('setItems')) {
            $product->load('setItems.component');
        }

        return $product->setItems->filter(fn (ProductSetItem $i) => $i->component !== null);
    }
}
