<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Xüsusiyyətlər (Material, Tərz...) və dəyərləri; qruplar */
class AttributeController extends Controller
{
    public function index()
    {
        return view('admin.attributes.index', [
            'groups' => AttributeGroup::query()->orderBy('sort')->get(),
            'attributes' => Attribute::query()->with(['values', 'group'])->orderBy('sort')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.attributes.form', ['attribute' => new Attribute, 'groups' => AttributeGroup::query()->orderBy('sort')->get()]);
    }

    public function edit(Attribute $attribute)
    {
        return view('admin.attributes.form', ['attribute' => $attribute->load('values'), 'groups' => AttributeGroup::query()->orderBy('sort')->get()]);
    }

    public function store(Request $request)
    {
        $attribute = new Attribute;
        $this->save($request, $attribute);

        return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'Xüsusiyyət yaradıldı.');
    }

    public function update(Request $request, Attribute $attribute)
    {
        $this->save($request, $attribute);

        return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'Yadda saxlanıldı.');
    }

    protected function save(Request $request, Attribute $attribute): void
    {
        $request->validate($this->translationRules(['name'], ['name']) + ['attribute_group_id' => 'nullable|exists:attribute_groups,id']);

        DB::transaction(function () use ($request, $attribute) {
            $attribute->fill([
                'name' => $this->trans($request, 'name'),
                'attribute_group_id' => $request->integer('attribute_group_id') ?: null,
                'sort' => $request->integer('sort'),
            ])->save();

            $keep = [];
            foreach (array_values((array) $request->input('values', [])) as $i => $row) {
                $value = array_filter((array) ($row['value'] ?? []), fn ($v) => trim((string) $v) !== '');
                if (! $value) {
                    continue;
                }
                $model = ! empty($row['id']) ? $attribute->values()->find($row['id']) : null;
                $model ??= $attribute->values()->make();
                $model->fill(['value' => $value, 'sort' => $i])->save();
                $keep[] = $model->id;
            }
            $attribute->values()->whereNotIn('id', $keep)->delete();
        });
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('admin.attributes.index')->with('success', 'Xüsusiyyət silindi.');
    }

    public function storeGroup(Request $request)
    {
        $request->validate($this->translationRules(['name'], ['name']));
        AttributeGroup::query()->create(['name' => $this->trans($request, 'name'), 'sort' => $request->integer('sort')]);

        return back()->with('success', 'Qrup yaradıldı.');
    }

    public function destroyGroup(AttributeGroup $group)
    {
        $group->delete();

        return back()->with('success', 'Qrup silindi.');
    }
}
