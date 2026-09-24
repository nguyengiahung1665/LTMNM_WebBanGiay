@extends('admin.layouts.admin')

@section('title', 'Chi tiết đơn hàng')

@section('content')
@php
    $labels = ['pending' => 'Chờ xác nhận', 'processing' => 'Đang xử lý', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
    $colors = ['pending' => 'yellow', 'processing' => 'blue', 'completed' => 'green', 'cancelled' => 'red'];
    $allowed = ['pending' => ['processing', 'cancelled'], 'processing' => ['completed', 'cancelled'], 'completed' => [], 'cancelled' => []][$order->status] ?? [];
@endphp
<div class="page-header"><h2>📦 Chi tiết đơn hàng #{{ $order->order_code }}</h2><a href="/admin/don-hang" class="btn btn-secondary">← Quay lại</a></div>
<div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(300px,1fr);gap:24px" class="order-detail-grid">
    <div class="card">
        <h3 style="font-weight:600;margin-bottom:16px">Sản phẩm đã đặt</h3>
        <div class="table-wrap"><table><thead><tr><th>Sản phẩm</th><th>SKU / Size / Màu</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr></thead><tbody>
        @foreach($order->items as $item)
            <tr><td>{{ $item->product_name }}</td><td style="font-size:13px">{{ $item->variant_sku ?? $item->variant?->sku ?? '—' }}<br>{{ $item->variant_size ? 'Size '.$item->variant_size : '' }} {{ $item->variant_color_name ? '/ '.$item->variant_color_name : '' }}</td><td>{{ number_format($item->product_price) }}₫</td><td>{{ $item->quantity }}</td><td style="font-weight:600">{{ number_format($item->subtotal) }}₫</td></tr>
        @endforeach
        </tbody></table></div>
    </div>
    <div>
        <div class="card" style="margin-bottom:24px">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px"><h3 style="font-weight:600">Trạng thái đơn hàng</h3><span class="badge badge-{{ $colors[$order->status] ?? 'yellow' }}">{{ $labels[$order->status] ?? $order->status }}</span></div>
            @if($order->status === 'cancelled')
                <div class="status-cancelled" style="margin-top:18px">Đã hủy</div>
            @else
                <div class="status-flow">
                    @foreach(['pending', 'processing', 'completed'] as $index => $status)
                        @php $currentIndex = array_search($order->status, ['pending','processing','completed'], true); @endphp
                        <div class="status-step {{ $status === $order->status ? 'is-active' : (($currentIndex !== false && $index < $currentIndex) ? 'is-done' : '') }}">{{ $labels[$status] }}</div>
                    @endforeach
                </div>
            @endif
            @if(count($allowed))<button type="button" class="btn btn-primary w-full" id="open-status-modal" style="justify-content:center">Cập nhật trạng thái</button>@else<p style="margin-top:16px;color:#64748b;font-size:13px">Đơn hàng đã ở trạng thái cuối và không thể chuyển tiếp.</p>@endif
        </div>
        <div class="card">
            <h3 style="font-weight:600;margin-bottom:16px">Thông tin khách hàng</h3>
            <p style="font-size:14px;margin-bottom:6px"><strong>{{ $order->full_name }}</strong></p><p style="font-size:14px;color:#666;margin-bottom:4px">📞 {{ $order->phone }}</p><p style="font-size:14px;color:#666;margin-bottom:4px">✉️ {{ $order->email }}</p><p style="font-size:14px;color:#666">📍 {{ $order->address }}</p>
            @if($order->note)<p style="font-size:14px;margin-top:8px;padding:8px;background:#f9f9f9;border-radius:6px">📝 Ghi chú: {{ $order->note }}</p>@endif
            <hr style="margin:16px 0"><div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px"><span style="color:#666">Tạm tính</span><span>{{ number_format($order->total_price - $order->shipping_fee) }}₫</span></div><div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:6px"><span style="color:#666">Phí ship</span><span>{{ $order->shipping_fee > 0 ? number_format($order->shipping_fee).'₫' : 'Miễn phí' }}</span></div><div style="display:flex;justify-content:space-between;font-size:20px;font-weight:700;padding-top:8px;border-top:2px solid #f0f0f0"><span>Tổng</span><span style="color:#e94560">{{ number_format($order->total_price) }}₫</span></div>
        </div>
    </div>
</div>

@if(count($allowed))
<div class="app-modal" id="status-modal" hidden aria-hidden="true"><div class="app-modal__overlay" data-status-close></div><div class="app-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="status-modal-title"><button type="button" class="app-modal__close" data-status-close aria-label="Đóng">&times;</button><h2 id="status-modal-title">Cập nhật trạng thái</h2><p>Trạng thái hiện tại: <strong>{{ $labels[$order->status] }}</strong></p><form method="POST" action="/admin/don-hang/{{ $order->id }}/cap-nhat" id="status-form">@csrf<div class="form-group" style="margin-top:18px"><label for="status">Trạng thái mới</label><select name="status" id="status" class="form-control">@foreach($allowed as $status)<option value="{{ $status }}">{{ $labels[$status] }}</option>@endforeach</select><small id="status-description" style="display:block;margin-top:8px;color:#64748b"></small></div><div class="app-modal__actions"><button type="button" class="btn btn-secondary" data-status-close>Hủy</button><button type="button" class="btn btn-primary" id="review-status-change">Xác nhận cập nhật</button></div></form></div></div>
@endif
@endsection

@push('styles')<style>@media(max-width:900px){.order-detail-grid{grid-template-columns:1fr!important}}</style>@endpush
@push('scripts')
@if(count($allowed))
<script>
(() => { const modal=document.getElementById('status-modal'),select=document.getElementById('status'),form=document.getElementById('status-form'); const labels=@json($labels); const descriptions={processing:'Đơn hàng sẽ được chuyển sang khâu xử lý.',completed:'Đơn hàng được ghi nhận đã hoàn thành.',cancelled:'Đơn hàng bị hủy và tồn kho sản phẩm sẽ được hoàn lại.'}; const close=()=>{modal.hidden=true;document.body.classList.remove('modal-open')}; const open=()=>{modal.hidden=false;document.body.classList.add('modal-open');select.focus()}; const update=()=>document.getElementById('status-description').textContent=descriptions[select.value]||''; document.getElementById('open-status-modal').addEventListener('click',open); document.querySelectorAll('[data-status-close]').forEach(el=>el.addEventListener('click',close)); select.addEventListener('change',update); update(); document.getElementById('review-status-change').addEventListener('click',()=>{ close(); const cancelling=select.value==='cancelled'; ConfirmModal.open({title:cancelling?'Xác nhận hủy đơn hàng':'Xác nhận cập nhật',message:cancelling?'Bạn có chắc muốn hủy đơn hàng này?\nTồn kho của các sản phẩm trong đơn sẽ được hoàn lại.':`Bạn có chắc muốn chuyển đơn hàng #{{ $order->order_code }} từ '${labels['{{ $order->status }}']}' sang '${labels[select.value]}'?`,confirmText:cancelling?'Hủy đơn hàng':'Cập nhật',type:cancelling?'danger':'primary',onConfirm:()=>form.submit()}); }); })();
</script>
@endif
@endpush
