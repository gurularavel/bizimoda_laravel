<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Support\Locales;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/** Bütün dillər üçün hreflang alternativləri ilə sitemap.xml */
class SitemapController extends Controller
{
    public function __invoke()
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(6), function () {
            $sitemap = Sitemap::create();
            $locales = Locales::codes();

            $add = function (callable $urlFor, $lastmod = null, float $priority = 0.5) use ($sitemap, $locales) {
                foreach ($locales as $locale) {
                    $url = Url::create($urlFor($locale))->setPriority($priority);
                    if ($lastmod) {
                        $url->setLastModificationDate($lastmod);
                    }
                    foreach ($locales as $alt) {
                        $url->addAlternate($urlFor($alt), $alt);
                    }
                    $sitemap->add($url);
                }
            };

            $add(fn ($l) => route('front.home', ['locale' => $l]), null, 1.0);
            Category::query()->active()->get()->each(fn ($c) => $add(fn ($l) => $c->url($l), $c->updated_at, 0.8));
            Product::query()->visible()->with('mainCategory')->get()->each(fn ($p) => $add(fn ($l) => $p->url($l), $p->updated_at, 0.7));
            Page::query()->where('is_active', true)->get()->each(fn ($p) => $add(fn ($l) => $p->url($l), $p->updated_at, 0.4));
            $add(fn ($l) => route('front.blog', ['locale' => $l]), null, 0.5);
            BlogPost::query()->published()->get()->each(fn ($p) => $add(fn ($l) => $p->url($l), $p->updated_at, 0.5));

            return $sitemap->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
