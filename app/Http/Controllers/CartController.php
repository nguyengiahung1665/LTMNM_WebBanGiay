<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = CartItem::with(['product', 'variant'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->final_price * $item->quantity;
        });

        return view('cart.index', compact('cartItems', 'subtotal'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $product = Product::findOrFail($data['product_id']);

        // Sản phẩm chưa được nhập biến thể (size/màu/tồn kho) nào trong admin
        // -> coi như chưa có dữ liệu tồn kho, KHÔNG cho đặt để tránh bán không giới hạn.
        if ($product->variants()->doesntExist()) {
            return back()->with('error', 'Sản phẩm này hiện chưa cập nhật thông tin tồn kho, vui lòng liên hệ shop để được hỗ trợ!');
        }

        // Sản phẩm có biến thể nhưng khách chưa chọn size/màu cụ thể
        if (empty($data['product_variant_id'])) {
            return back()->with('error', 'Vui lòng chọn size/màu trước khi thêm vào giỏ hàng!');
        }

        // Kiểm tra tồn kho của đúng biến thể (size+màu cụ thể) được chọn
        $variantStock = null;
        if (!empty($data['product_variant_id'])) {
            $variant = ProductVariant::where('product_id', $product->id)
                ->find($data['product_variant_id']);

            if (!$variant) {
                return back()->with('error', 'Biến thể sản phẩm không hợp lệ!');
            }
            $variantStock = $variant->stock;
        }

        // Kiểm tra xem đã có item này trong giỏ chưa
        $existing = CartItem::where('user_id', Auth::id())
            ->where('product_id', $data['product_id'])
            ->where('product_variant_id', $data['product_variant_id'] ?? null)
            ->first();

        if ($existing) {
            $newQty = $existing->quantity + $data['quantity'];
            if ($variantStock !== null && $newQty > $variantStock) {
                return back()->with('error', 'Số lượng vượt quá tồn kho!');
            }
            $existing->update(['quantity' => $newQty]);
        } else {
            if ($variantStock !== null && $data['quantity'] > $variantStock) {
                return back()->with('error', 'Số lượng vượt quá tồn kho!');
            }
            CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
                'quantity' => $data['quantity'],
            ]);
        }

        return redirect('/gio-hang')->with('success', 'Đã thêm vào giỏ hàng!');
    }

    public function update(Request $request, $id)
    {
        $item = CartItem::where('user_id', Auth::id())->findOrFail($id);

        $data = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        if ($item->variant && $data['quantity'] > $item->variant->stock) {
            return back()->with('error', "Số lượng vượt quá tồn kho (còn {$item->variant->stock} sản phẩm).");
        }

        $item->update(['quantity' => $data['quantity']]);

        return back()->with('success', 'Đã cập nhật số lượng!');
    }

    public function remove($id)
    {
        $item = CartItem::where('user_id', Auth::id())->findOrFail($id);
        $item->delete();

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
    }
}
