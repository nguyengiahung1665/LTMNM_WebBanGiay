<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index(Request $request)
    {
        $items = CartItem::with(['product', 'variant'])->where('user_id', $request->user()->id)->latest()->get();
        return response()->json(['success' => true, 'message' => 'Lấy giỏ hàng thành công.', 'data' => CartItemResource::collection($items), 'meta' => ['subtotal' => $items->sum(fn (CartItem $item) => $item->subtotal)]]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['product_id' => 'required|integer', 'product_variant_id' => 'required|integer', 'quantity' => 'required|integer|min:1|max:99']);
        $item = $this->cartService->add($request->user(), $data['product_id'], $data['product_variant_id'], $data['quantity']);
        return response()->json(['success' => true, 'message' => 'Đã thêm vào giỏ hàng.', 'data' => new CartItemResource($item)], 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1|max:99']);
        $item = $this->cartService->update($request->user(), $id, $data['quantity']);
        return response()->json(['success' => true, 'message' => 'Đã cập nhật giỏ hàng.', 'data' => new CartItemResource($item)]);
    }

    public function destroy(Request $request, int $id)
    {
        $item = CartItem::where('user_id', $request->user()->id)->find($id);
        if (! $item) return response()->json(['success' => false, 'message' => 'Không tìm thấy mục giỏ hàng của bạn.'], 404);
        $item->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.', 'data' => null]);
    }
}
