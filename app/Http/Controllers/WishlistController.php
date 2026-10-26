<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlist = Auth::user()->wishlists()
            ->with(['product' => fn ($query) => $query
                ->with(['category', 'brand', 'images'])
                ->withAvg('reviews', 'rating')])
            ->latest()
            ->get();

        return view('wishlist.index', compact('wishlist'));
    }

    public function toggle($productId)
    {
        $product = Product::findOrFail($productId);

        $existing = Auth::user()->wishlists()
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $wished = false;
        } else {
            Auth::user()->wishlists()->create(['product_id' => $productId]);
            $wished = true;
        }

        if (request()->wantsJson()) {
            return response()->json(['wished' => $wished]);
        }

        return back();
    }
}
