<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProductReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = ProductReview::query()->with('product')
            ->when($request->query('status') === 'pending', fn ($q) => $q->where('is_approved', false))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function toggle(ProductReview $review)
    {
        $review->update(['is_approved' => ! $review->is_approved]);

        return back()->with('success', $review->is_approved ? 'Rəy təsdiqləndi.' : 'Rəy gizlədildi.');
    }

    public function destroy(ProductReview $review)
    {
        $review->delete();

        return back()->with('success', 'Rəy silindi.');
    }
}
