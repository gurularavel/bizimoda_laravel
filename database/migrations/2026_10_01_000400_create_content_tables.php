<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('content')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('template', 30)->default('default'); // default|full-width|contact
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30); // text|image|banner|products|html|faq|form
            $table->json('data')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('slug');
            $table->json('description')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->json('slug');
            $table->json('excerpt')->nullable();
            $table->json('content')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('tags')->nullable();
            $table->string('cover')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique(); // main|top|footer_1|footer_2|footer_3|mobile_top
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->json('title');
            // category|product|page|blog|blog_category|blog_post|route|url|none
            $table->string('type', 20)->default('url');
            $table->unsignedBigInteger('linkable_id')->nullable();
            $table->string('url', 500)->nullable();
            $table->boolean('target_blank')->default(false);
            // link|flyout|mega|group  — meqa menyu görünüşü
            $table->string('display', 10)->default('link');
            $table->unsignedTinyInteger('column')->default(1);
            $table->boolean('auto_children')->default(false);
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('banner_url', 500)->nullable();
            $table->string('css_class')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('name');
            $table->unsignedInteger('width')->default(1590);
            $table->unsignedInteger('height')->default(458);
            $table->timestamps();
        });

        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slider_id')->constrained()->cascadeOnDelete();
            $table->json('title')->nullable();
            $table->string('image');
            $table->string('image_mobile')->nullable();
            $table->string('link', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30); // slider|products|banners|html|blog
            $table->json('title')->nullable();
            $table->json('data')->nullable();
            $table->string('module_class')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['home_sections', 'slides', 'sliders', 'menu_items', 'menus', 'blog_posts', 'blog_categories', 'page_blocks', 'pages'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
