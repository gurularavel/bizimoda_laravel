<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            NestedSet::columns($table);
            $table->json('name');
            $table->json('slug');
            $table->json('description')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('image')->nullable();
            $table->string('banner')->nullable();
            $table->boolean('is_active')->default(true);
            // Kateqoriya filtr panelindəki "Altbaşlıqlar" siyahısında görünsün
            $table->boolean('show_in_filter')->default(true);
            // Öz filtr ayarı yoxdursa valideyn kateqoriyadan miras alsın
            $table->boolean('filter_inherit')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_group_id')->nullable()->constrained()->nullOnDelete();
            $table->json('name');
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->json('value');
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('type', 20)->default('select'); // color|select|radio|image
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_id')->constrained()->cascadeOnDelete();
            $table->json('name');
            $table->string('color', 20)->nullable();
            $table->string('image')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('category_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // price|subcategory|stock|brand|attribute|option
            $table->unsignedBigInteger('ref_id')->nullable(); // attribute_id / option_id
            $table->integer('sort')->default(0);
            $table->unique(['category_id', 'type', 'ref_id']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10)->default('simple')->index(); // simple|set|module
            $table->string('sku')->nullable()->index();
            $table->json('name');
            $table->json('slug');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->json('dimensions')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->foreignId('main_category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('old_price', 12, 2)->nullable();
            // Siyahı/filtr/sıralama üçün hesablanmış qiymət (dəstlərdə modulların cəmi)
            $table->decimal('computed_price', 12, 2)->default(0)->index();
            $table->decimal('computed_old_price', 12, 2)->nullable();
            $table->integer('stock_qty')->default(0);
            $table->string('stock_status', 20)->default('in_stock'); // in_stock|out_of_stock|preorder
            $table->unsignedInteger('min_qty')->default(1);
            $table->json('label')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->boolean('sold_separately')->default(true);
            $table->integer('sort')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'product_id']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->foreignId('option_value_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('attribute_value_product', function (Blueprint $table) {
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['attribute_value_id', 'product_id']);
        });

        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(true);
            $table->integer('sort')->default(0);
            $table->unique(['product_id', 'option_id']);
        });

        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_value_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_modifier', 12, 2)->default(0);
            $table->string('modifier_type', 10)->default('fixed'); // fixed|percent
            $table->integer('stock_qty')->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('sort')->default(0);
        });

        // Dəst (set) → modullar. Dəstin qiyməti modulların qiyməti × sayından formalaşır.
        Schema::create('product_set_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('set_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('default_qty')->default(1);
            $table->unsignedInteger('min_qty')->default(1);
            $table->unsignedInteger('max_qty')->nullable();
            $table->boolean('is_required')->default(false);
            $table->decimal('price_override', 12, 2)->nullable();
            $table->decimal('old_price_override', 12, 2)->nullable();
            $table->integer('sort')->default(0);
            $table->unique(['set_id', 'component_id']);
        });

        Schema::create('product_related', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_id')->constrained('products')->cascadeOnDelete();
            $table->primary(['product_id', 'related_id']);
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author');
            $table->unsignedTinyInteger('rating');
            $table->text('text');
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        foreach (['wishlists', 'product_reviews', 'product_related', 'product_set_items', 'product_option_values',
            'product_options', 'attribute_value_product', 'product_images', 'category_product', 'products',
            'category_filters', 'option_values', 'options', 'attribute_values', 'attributes', 'attribute_groups',
            'brands', 'categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
