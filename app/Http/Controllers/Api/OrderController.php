<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}
    public function index(Request $request) { return response()->json(['success' => true, 'message' => 'Lấy đơn hàng thành công.', 'data' => OrderResource::collection(Order::with('items')->where('user_id', $request->user()->id)->latest()->paginate(10))]); }
    public function show(Request $request, int $id) { $order = Order::with(['items.variant'])->where('user_id', $request->user()->id)->findOrFail($id); return response()->json(['success' => true, 'message' => 'Lấy đơn hàng thành công.', 'data' => new OrderResource($order)]); }
    public function store(Request $request) {
        $data = $request->validate(['full_name' => 'required|string|max:255', 'email' => 'required|email|max:255', 'phone' => 'required|string|max:20', 'address' => 'required|string|max:500', 'note' => 'nullable|string|max:1000']);
        $order = $this->orders->createFromCart($request->user(), $data);
        return response()->json(['success' => true, 'message' => 'Đặt hàng thành công.', 'data' => new OrderResource($order)], 201);
    }
}
