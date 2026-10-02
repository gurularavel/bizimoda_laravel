<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Services\MenuBuilder;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dillər: kodlar config/locales.php-də (az, ru, en) sabitdir; burada aktivlik, ad, sıra və default dil idarə olunur.
 */
class LanguageController extends Controller
{
    public function index()
    {
        foreach (config('locales.supported') as $code => $name) {
            Language::query()->firstOrCreate(['code' => $code], ['name' => $name, 'is_active' => false]);
        }

        return view('admin.languages.index', ['languages' => Language::query()->orderBy('sort')->get()]);
    }

    public function edit(Language $language)
    {
        return view('admin.languages.form', compact('language'));
    }

    public function update(Request $request, Language $language)
    {
        $request->validate(['name' => 'required|string|max:50', 'sort' => 'nullable|integer']);

        DB::transaction(function () use ($request, $language) {
            $isDefault = $request->boolean('is_default');
            if ($isDefault) {
                Language::query()->whereKeyNot($language->id)->update(['is_default' => false]);
            }
            $language->update([
                'name' => $request->input('name'),
                'sort' => $request->integer('sort'),
                'is_default' => $isDefault || ($language->is_default && ! Language::query()->whereKeyNot($language->id)->where('is_default', true)->exists()),
                'is_active' => $isDefault ? true : $request->boolean('is_active'),
            ]);
        });

        Locales::flush();
        MenuBuilder::flush();

        return redirect()->route('admin.languages.index')->with('success', 'Dil yeniləndi.');
    }
}
