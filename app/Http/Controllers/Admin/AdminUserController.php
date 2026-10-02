<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.admins.index', ['admins' => Admin::query()->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.admins.form', ['admin' => new Admin(['role' => 'manager', 'is_active' => true])]);
    }

    public function edit(Admin $admin)
    {
        return view('admin.admins.form', compact('admin'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        Admin::query()->create($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.admins.index')->with('success', 'Admin yaradıldı.');
    }

    public function update(Request $request, Admin $admin)
    {
        $data = $this->validated($request, $admin);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        // Özünü deaktiv etmək / rolunu endirmək olmaz
        if ($admin->id === auth('admin')->id()) {
            $data['role'] = $admin->role;
            $data['is_active'] = true;
        } else {
            $data['is_active'] = $request->boolean('is_active');
        }
        $admin->update($data);

        return redirect()->route('admin.admins.index')->with('success', 'Yadda saxlanıldı.');
    }

    protected function validated(Request $request, ?Admin $admin): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('admins')->ignore($admin?->id)],
            'role' => ['required', Rule::in(array_keys(Admin::ROLES))],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:8'],
        ]);
    }

    public function destroy(Admin $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'Özünüzü silə bilməzsiniz.');
        }
        $admin->delete();

        return back()->with('success', 'Admin silindi.');
    }
}
