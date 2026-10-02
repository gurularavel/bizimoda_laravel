<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 10)->default('percent'); // percent|fixed
            $table->decimal('value', 12, 2);
            $table->string('applies_to', 20)->default('products'); // all|products|categories
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('discount_product', function (Blueprint $table) {
            $table->foreignId('discount_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['discount_id', 'product_id']);
        });

        Schema::create('category_discount', function (Blueprint $table) {
            $table->foreignId('discount_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['discount_id', 'category_id']);
        });

        // Sifariş sətrində tətbiq olunmuş endirim (vahid üzrə)
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('unit_discount', 12, 2)->default(0)->after('unit_old_price');
            $table->string('discount_name')->nullable()->after('unit_discount');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $t) => $t->dropColumn(['unit_discount', 'discount_name']));
        Schema::dropIfExists('category_discount');
        Schema::dropIfExists('discount_product');
        Schema::dropIfExists('discounts');
    }
};
