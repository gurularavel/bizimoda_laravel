<?php

namespace App\Http\Controllers\Admin;

use App\Models\Option;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Opsiyonlar (rəng, ölçü...) və onların dəyərləri */
class OptionController extends Controller
{
    public function index()
    {
        return view('admin.options.index', ['options' => Option::query()->with('values')->orderBy('sort')->get()]);
    }

    public function create()
    {
        return view('admin.options.form', ['option' => new Option(['type' => 'color'])]);
    }

    public function edit(Option $option)
    {
        return view('admin.options.form', ['option' => $option->load('values')]);
    }

    public function store(Request $request)
    {
        $option = new Option;
        $this->save($request, $option);

        return redirect()->route('admin.options.edit', $option)->with('success', 'Opsiyon yaradıldı.');
    }

    public function update(Request $request, Option $option)
    {
        $this->save($request, $option);

        return redirect()->route('admin.options.edit', $option)->with('success', 'Yadda saxlanıldı.');
    }

    protected function save(Request $request, Option $option): void
    {
        $request->validate($this->translationRules(['name'], ['name']) + [
            'type' => ['required', Rule::in(array_keys(Option::TYPES))],
            'values' => 'array',
            'values.*.color' => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($request, $option) {
            $option->fill(['name' => $this->trans($request, 'name'), 'type' => $request->input('type'), 'sort' => $request->integer('sort')])->save();

            $keep = [];
            foreach (array_values((array) $request->input('values', [])) as $i => $row) {
                $name = array_filter((array) ($row['name'] ?? []), fn ($v) => trim((string) $v) !== '');
                if (! $name) {
                    continue;
                }
                $value = ! empty($row['id']) ? $option->values()->find($row['id']) : null;
                $value ??= $option->values()->make();
                $image = $value->image;
                if ($request->hasFile("values.{$i}.image")) {
                    $image = app(ImageService::class)->store($request->file("values.{$i}.image"), 'options');
                }
                $value->fill(['name' => $name, 'color' => $row['color'] ?? null, 'image' => $image, 'sort' => $i])->save();
                $keep[] = $value->id;
            }
            $option->values()->whereNotIn('id', $keep)->delete();
        });
    }

    public function destroy(Option $option)
    {
        $option->delete();

        return redirect()->route('admin.options.index')->with('success', 'Opsiyon silindi.');
    }
}
