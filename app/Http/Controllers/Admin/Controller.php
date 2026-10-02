<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller as BaseController;
use App\Services\ImageService;
use App\Support\Locales;
use Illuminate\Http\Request;

abstract class Controller extends BaseController
{
    /** Formadan gələn tərcümələr: name[az], name[ru], ... → ['az' => ..., 'ru' => ...] (boşlar atılır) */
    protected function trans(Request $request, string $field): array
    {
        $values = (array) $request->input($field, []);
        $out = [];
        foreach (Locales::codes() as $locale) {
            $value = $values[$locale] ?? null;
            if (is_string($value) && trim($value) !== '') {
                $out[$locale] = trim($value);
            }
        }

        return $out;
    }

    /** Bir neçə tərcümə sahəsi birdən */
    protected function transMany(Request $request, array $fields): array
    {
        $out = [];
        foreach ($fields as $field) {
            $out[$field] = $this->trans($request, $field);
        }

        return $out;
    }

    /** Default dildə ad məcburidir */
    protected function translationRules(array $fields, array $required = []): array
    {
        $rules = [];
        $default = Locales::default();
        foreach ($fields as $field) {
            $rules[$field] = 'array';
            $rules[$field.'.*'] = 'nullable|string';
            if (in_array($field, $required, true)) {
                $rules[$field.'.'.$default] = 'required|string|max:255';
            }
        }

        return $rules;
    }

    /**
     * Şəkil sahəsi: yeni fayl yüklənibsə saxlanılır, "{field}_remove" işarələnibsə silinir.
     */
    protected function image(Request $request, string $field, ?string $current, string $folder): ?string
    {
        $images = app(ImageService::class);

        if ($request->hasFile($field)) {
            $request->validate([$field => 'image|max:8192']);
            $images->delete($current);

            return $images->store($request->file($field), $folder);
        }

        if ($request->boolean($field.'_remove')) {
            $images->delete($current);

            return null;
        }

        return $current;
    }
}
