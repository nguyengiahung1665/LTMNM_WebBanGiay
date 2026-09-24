<?php

namespace App\Http\Controllers;

use App\Events\NewOrderPlaced;
use App\Mail\OrderPlacedMail;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = CartItem::with(['product', 'variant'])
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect('/gio-hang')->with('error', 'Giỏ hàng trống!');
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->final_price * $item->quantity;
        });

        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $total = $subtotal + $shippingFee;
        $locations = $this->locations();

        return view('checkout.index', compact('cartItems', 'subtotal', 'shippingFee', 'total', 'locations'));
    }

    public function placeOrder(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'province_code' => 'required|string|max:10',
            'ward_code' => 'required|string|max:10',
            'address_detail' => 'required|string|max:255',
            'note' => 'nullable|string|max:1000',
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên người nhận.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'ward_code.required' => 'Vui lòng chọn phường/xã.',
            'address_detail.required' => 'Vui lòng nhập địa chỉ chi tiết.',
        ]);

        $province = collect($this->locations())->firstWhere('code', $data['province_code']);
        if (! $province) {
            throw ValidationException::withMessages([
                'province_code' => ['Tỉnh/thành phố đã chọn không hợp lệ.'],
            ]);
        }

        $ward = collect($province['wards'])->firstWhere('code', $data['ward_code']);
        if (! $ward) {
            throw ValidationException::withMessages([
                'ward_code' => ['Phường/xã không thuộc tỉnh/thành phố đã chọn.'],
            ]);
        }

        $fullAddress = implode(', ', [
            trim($data['address_detail']),
            $ward['name'],
            $province['name'],
        ]);

        $cartItems = CartItem::with(['product', 'variant'])
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Giỏ hàng trống!');
        }

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->final_price * $item->quantity;
        });

        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $total = $subtotal + $shippingFee;

        // Tạo mã đơn hàng
        $orderCode = 'DH' . date('Ymd') . strtoupper(\Illuminate\Support\Str::random(6));

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_code' => $orderCode,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $fullAddress,
                'note' => $data['note'] ?? null,
                'shipping_fee' => $shippingFee,
                'total_price' => $total,
                'status' => 'pending',
                'payment_method' => 'cod',
            ]);

            foreach ($cartItems as $item) {
                // [KHO] Giữ chỗ tồn kho ngay lúc đặt hàng bằng update có điều kiện
                // (WHERE stock >= quantity) trên đúng 1 biến thể (size+màu cụ thể).
                // Đây là thao tác atomic ở tầng DB nên tránh được race condition
                // khi nhiều người cùng đặt 1 biến thể sắp hết hàng, mà không cần
                // lock thủ công.
                if ($item->product_variant_id) {
                    $affected = $item->variant()
                        ->where('stock', '>=', $item->quantity)
                        ->decrement('stock', $item->quantity);

                    if (!$affected) {
                        throw new \RuntimeException(
                            "Sản phẩm \"{$item->product->name}\" ({$item->variant->label}) không đủ số lượng trong kho (chỉ còn {$item->variant->stock} cái)."
                        );
                    }
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'product_price' => $item->product->final_price,
                    'quantity' => $item->quantity,
                ]);
            }

            // Xóa giỏ hàng
            CartItem::where('user_id', Auth::id())->delete();

            DB::commit();

            // [QUEUE] OrderPlacedMail implements ShouldQueue -> lệnh send()
            // này chỉ ghi 1 job vào bảng `jobs` rồi trả về ngay lập tức,
            // việc gửi email thật sự do "php artisan queue:work" xử lý ở nền.
            Mail::to($order->email)->send(new OrderPlacedMail($order));

            // [SOCKET] Event implements ShouldBroadcast -> cũng được đẩy
            // vào queue rồi mới bắn qua WebSocket, không làm chậm response.
            broadcast(new NewOrderPlaced($order))->toOthers();

            return redirect('/dat-hang/thanh-cong/' . $order->id);

        } catch (\RuntimeException $e) {
            // Lỗi hết hàng / không đủ tồn kho -> hiện đúng thông báo cho khách
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại!');
        }
    }

    public function success($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);
        return view('checkout.success', compact('order'));
    }

    private function locations(): array
    {
        $path = resource_path('data/vietnam-addresses.json');

        if (! is_file($path)) {
            return [];
        }

        return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }
}
