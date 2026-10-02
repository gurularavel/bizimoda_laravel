<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Support\Locales;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Admin paneldə qurulan menyuları (meqa menyu, footer, top linklər) front üçün
 * ağac strukturuna çevirir: [title, url, target, display, column, banner, children...].
 */
class MenuBuilder
{
    public function tree(string $key, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return Cache::rememberForever("menu.{$key}.{$locale}", function () use ($key, $locale) {
            $menu = Menu::query()->where('key', $key)->first();
            if (! $menu) {
                return [];
            }

            $items = $menu->items()->where('is_active', true)->get();
            $linkables = $this->loadLinkables($items);

            return $this->branch($items, null, $linkables, $locale);
        });
    }

    protected function branch(Collection $items, ?int $parentId, array $linkables, string $locale): array
    {
        return $items->where('parent_id', $parentId)->sortBy('sort')->values()
            ->map(function (MenuItem $item) use ($items, $linkables, $locale) {
                $linkable = $linkables[$item->type][$item->linkable_id] ?? null;
                $children = $this->branch($items, $item->id, $linkables, $locale);

                if ($item->auto_children && $item->type === 'category' && $linkable) {
                    $children = array_merge($children, $this->categoryChildren($linkable, $item->display, $locale));
                }

                // Səhifələrin adı "title", kateqoriya/məhsulların adı "name" sahəsindədir
                $linkableField = $linkable ? collect(['name', 'title'])->first(fn ($f) => isset($linkable->{$f})) : null;
                $title = $item->getTranslation('title', $locale, false)
                    ?: ($linkableField ? $linkable->getTranslation($linkableField, $locale) : $item->getTranslation('title', Locales::default()));

                return [
                    'id' => $item->id,
                    'title' => $title,
                    'url' => $item->resolveUrl($linkable, $locale),
                    'target' => $item->target_blank ? '_blank' : null,
                    'display' => $item->display,
                    'column' => (int) $item->column,
                    'css_class' => $item->css_class,
                    'icon' => $item->icon,
                    'image' => $item->image,
                    'banner_image' => $item->banner_image,
                    'banner_url' => $item->banner_url,
                    'children' => $children,
                ];
            })->all();
    }

    /** "Alt kateqoriyaları avtomatik göstər" seçilibsə */
    protected function categoryChildren(Category $category, string $display, string $locale): array
    {
        $children = $category->children()->active()->orderBy('sort')->with(['children' => fn ($q) => $q->active()->orderBy('sort')])->get();
        $column = 1;

        return $children->map(function (Category $child) use ($display, $locale, &$column) {
            $grand = $child->children->map(fn (Category $g) => [
                'id' => 'c'.$g->id, 'title' => $g->getTranslation('name', $locale), 'url' => $g->url($locale), 'target' => null,
                'display' => 'link', 'column' => 1, 'css_class' => null, 'icon' => null, 'image' => $g->image,
                'banner_image' => null, 'banner_url' => null, 'children' => [],
            ])->all();

            return [
                'id' => 'c'.$child->id,
                'title' => $child->getTranslation('name', $locale),
                'url' => $child->url($locale),
                'target' => null,
                'display' => $display === 'mega' ? 'group' : 'link',
                'column' => $column++,
                'css_class' => null, 'icon' => null, 'image' => $child->image,
                'banner_image' => null, 'banner_url' => null,
                'children' => $grand,
            ];
        })->all();
    }

    protected function loadLinkables(Collection $items): array
    {
        $result = [];
        foreach ($items->groupBy('type') as $type => $group) {
            $model = $group->first()->linkableModel();
            if (! $model) {
                continue;
            }
            $ids = $group->pluck('linkable_id')->filter()->unique();
            $query = $model::query()->whereIn('id', $ids);
            if ($type === 'product') {
                $query->with('mainCategory');
            }
            $result[$type] = $query->get()->keyBy('id')->all();
        }

        return $result;
    }

    public static function flush(): void
    {
        foreach (array_keys(Menu::LOCATIONS) as $key) {
            foreach (array_keys(config('locales.supported')) as $locale) {
                Cache::forget("menu.{$key}.{$locale}");
            }
        }
    }
}
