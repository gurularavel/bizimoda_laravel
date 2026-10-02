<?php

namespace App\Http\Controllers\Admin;

use App\Services\ImageService;
use Illuminate\Http\Request;

/** TinyMCE redaktorundan şəkil yükləmə: {location: url} */
class UploadController extends Controller
{
    public function __invoke(Request $request, ImageService $images)
    {
        $request->validate(['file' => 'required|image|max:8192']);
        $path = $images->store($request->file('file'), 'editor');

        return response()->json(['location' => $images->url($path)]);
    }
}
