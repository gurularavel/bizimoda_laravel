<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Support\Locales;
use Illuminate\Http\Request;

/**
 * /{locale}/{slug} → əvvəlcə kateqoriya, sonra səhifə. Slug başqa dildə tapılarsa
 * cari dildəki düzgün slug-a 301 yönləndirilir.
 */
class SlugController extends Controller
{
    public function __invoke(Request $request, string $slug)
    {
        $locale = app()->getLocale();

        if ($category = Category::query()->active()->whereSlug($slug, $locale)->first()) {
            return app(CatalogController::class)->category($request, $category);
        }

        if ($page = Page::query()->where('is_active', true)->whereSlug($slug, $locale)->first()) {
            return $this->page($page);
        }

        // Başqa dildəki slug ilə gəlibsə (məs. dil dəyişdirəndə) düzgün URL-ə yönləndir
        foreach (Locales::codes() as $other) {
            if ($other === $locale) {
                continue;
            }
            if ($category = Category::query()->active()->whereSlug($slug, $other)->first()) {
                return redirect($category->url($locale), 301);
            }
            if ($page = Page::query()->where('is_active', true)->whereSlug($slug, $other)->first()) {
                return redirect($page->url($locale), 301);
            }
        }

        abort(404);
    }

    public function contact()
    {
        $page = Page::query()->where('is_active', true)->where('template', 'contact')->orderBy('sort')->first();
        if ($page) {
            return redirect($page->url());
        }

        return view('front.page.contact', [
            'page' => null,
            'htmlClass' => 'route-information-contact page-contact layout-8',
        ]);
    }

    protected function page(Page $page)
    {
        $page->load('blocks');
        $alternates = [];
        foreach (Locales::codes() as $code) {
            $alternates[$code] = $page->url($code);
        }

        $view = $page->template === 'contact' ? 'front.page.contact' : 'front.page.show';

        return view($view, [
            'page' => $page,
            'alternates' => $alternates,
            'blockProducts' => $page->blocks->where('type', 'products')
                ->mapWithKeys(fn ($b) => [$b->id => HomeController::productsFor($b->data ?? [])]),
            'htmlClass' => 'route-information-information information-'.$page->id.' '.($page->template === 'contact' ? 'page-contact layout-8' : ($page->template === 'full-width' ? 'layout-8' : 'layout-11 one-column column-right')),
        ]);
    }
}
