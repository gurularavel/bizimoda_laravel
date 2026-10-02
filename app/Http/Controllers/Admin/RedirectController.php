<?php

namespace App\Http\Controllers\Admin;

use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Köhnə (OpenCart) URL-lərdən yenilərinə 301/302 yönləndirmələr */
class RedirectController extends Controller
{
    public function index()
    {
        return view('admin.redirects.index', ['redirects' => Redirect::query()->latest()->paginate(50)]);
    }

    public function create()
    {
        return view('admin.redirects.form', ['redirect' => new Redirect(['code' => 301])]);
    }

    public function edit(Redirect $redirect)
    {
        return view('admin.redirects.form', compact('redirect'));
    }

    public function store(Request $request)
    {
        Redirect::query()->create($this->validated($request, null));

        return redirect()->route('admin.redirects.index')->with('success', 'Yönləndirmə əlavə edildi.');
    }

    public function update(Request $request, Redirect $redirect)
    {
        $redirect->update($this->validated($request, $redirect));

        return redirect()->route('admin.redirects.index')->with('success', 'Yadda saxlanıldı.');
    }

    protected function validated(Request $request, ?Redirect $redirect): array
    {
        $request->merge(['from_path' => '/'.ltrim((string) parse_url((string) $request->input('from_path'), PHP_URL_PATH).(($q = parse_url((string) $request->input('from_path'), PHP_URL_QUERY)) ? '?'.$q : ''), '/')]);

        return $request->validate([
            'from_path' => ['required', 'string', 'max:255', Rule::unique('redirects', 'from_path')->ignore($redirect?->id)],
            'to_path' => 'required|string|max:255',
            'code' => ['required', Rule::in([301, 302])],
        ]);
    }

    public function destroy(Redirect $redirect)
    {
        $redirect->delete();

        return back()->with('success', 'Silindi.');
    }
}
