<?php

namespace Tests\Feature;

use App\Models\Option;
use App\Models\Product;
use App\Services\SetPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SetPricingTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function set(): Product
    {
        return Product::query()->where('sku', 'AYP-SET')->firstOrFail();
    }

    protected function moduleId(string $sku): int
    {
        return Product::query()->where('sku', $sku)->value('id');
    }

    public function test_set_price_is_sum_of_modules_with_default_quantities(): void
    {
        $set = $this->set();

        // 316.3 + 2 × 35.1 + 368 + 133
        $this->assertEquals(887.50, (float) $set->computed_price);
        // 333 + 2 × 37 + 386 + 140
        $this->assertEquals(933.00, (float) $set->computed_old_price);
    }

    public function test_module_below_minimum_quantity_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        app(SetPricingService::class)->validate($this->set(), [
            'components' => [$this->moduleId('AYP-TUMB') => 1], // tumba min 2
            'options' => [Option::query()->value('id') => $this->colorValue('Bəyaz')],
        ]);
    }

    public function test_optional_module_can_be_removed_and_price_recalculated(): void
    {
        $pricing = app(SetPricingService::class);
        $set = $this->set();

        $config = $pricing->validate($set, [
            'components' => [$this->moduleId('AYP-TUMB') => 0],
            'options' => [Option::query()->value('id') => $this->colorValue('Bəyaz')],
        ]);

        $this->assertEquals(817.30, $pricing->price($set, $config)['unit_price']);
    }

    public function test_required_module_cannot_be_removed(): void
    {
        try {
            app(SetPricingService::class)->validate($this->set(), [
                'components' => [$this->moduleId('AYP-CARP') => 0],
                'options' => [Option::query()->value('id') => $this->colorValue('Bəyaz')],
            ]);
            $this->fail('Required module removal must fail');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('components.'.$this->moduleId('AYP-CARP'), $e->errors());
        }
    }

    public function test_quantity_above_maximum_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        app(SetPricingService::class)->validate($this->set(), [
            'components' => [$this->moduleId('AYP-TUMB') => 6], // max 4
            'options' => [Option::query()->value('id') => $this->colorValue('Bəyaz')],
        ]);
    }

    public function test_percent_option_modifier_applies_to_whole_set(): void
    {
        $pricing = app(SetPricingService::class);
        $set = $this->set();
        $config = $pricing->validate($set, ['options' => [Option::query()->value('id') => $this->colorValue('Boz')]]);

        // 887.5 × 1.03
        $this->assertEquals(914.13, $pricing->price($set, $config)['unit_price']);
    }

    public function test_changing_module_price_refreshes_set_listing_price(): void
    {
        $nightstand = Product::query()->where('sku', 'AYP-TUMB')->first();
        $nightstand->update(['price' => 40]);

        $this->assertEquals(316.3 + 80 + 368 + 133, (float) $this->set()->fresh()->computed_price);
    }

    protected function colorValue(string $az): int
    {
        return Option::query()->first()->values->first(fn ($v) => $v->getTranslation('name', 'az') === $az)->id;
    }
}
