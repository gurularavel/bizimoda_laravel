<?php

namespace App\Http\Controllers\Admin;

use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $items = FormSubmission::query()->with('product')
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.forms.index', compact('items'));
    }

    public function show(FormSubmission $submission)
    {
        $submission->update(['is_read' => true]);

        return view('admin.forms.show', ['item' => $submission->load('product')]);
    }

    public function destroy(FormSubmission $submission)
    {
        $submission->delete();

        return redirect()->route('admin.forms.index')->with('success', 'Silindi.');
    }
}
