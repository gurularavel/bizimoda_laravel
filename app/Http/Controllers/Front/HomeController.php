<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\HomeSection;
use App\Models\Product;
use App\Models\Slider;

class HomeController extends Controller
{
    public function __invoke()
    {
        $sections = HomeSection::query()->where('is_active', true)->orderBy('sort')->get()
            ->map(function (HomeSection $section) {
                $data = $section->data ?? [];
                $section->payload = match ($section->type) {
                    'slider' => Slider::query()->with('activeSlides')->find($data['slider_id'] ?? null),
                    'products' => $this->products($data),
                    'blog' => BlogPost::query()->published()->latest('published_at')->limit((int) ($data['limit'] ?? 4))->get(),
                    default => null,
                };

                return $section;
            });

        return view('front.home', [
            'sections' => $sections,
            'htmlClass' => 'route-common-home layout-1',
        ]);
    }

    public static function productsFor(array $data)
    {
        return (new self)->products($data);
    }

    protected function products(array $data)
    {
        $limit = (int) ($data['limit'] ?? 12) ?: 12;
        $query = Product::query()->visible()->forListing();

        switch ($data['source'] ?? 'featured') {
            case 'special':
                $query->onSale()->orderBy('sort');
                break;
            case 'new':
                $query->latest();
                break;
            case 'popular':
                $query->orderByDesc('views');
                break;
            case 'category':
                $query->whereHas('categories', fn ($q) => $q->where('categories.id', $data['category_id'] ?? 0))->orderBy('sort');
                break;
            case 'ids':
                $ids = array_map('intval', (array) ($data['ids'] ?? []));
                $query->whereIn('id', $ids);
                if ($ids) {
                    $query->orderByRaw('FIELD(id, '.implode(',', $ids).')');
                }
                break;
            default:
                $query->where('is_featured', true)->orderBy('sort');
        }

        return $query->limit($limit)->get();
    }
}
