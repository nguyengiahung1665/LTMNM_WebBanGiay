<div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="false">
    @foreach(['success', 'error', 'warning', 'info'] as $type)
        @if(session($type))
            <div class="app-toast app-toast--{{ $type }}" data-auto-dismiss="7000" role="status">
                <span class="app-toast__icon" aria-hidden="true"></span>
                <div class="app-toast__message">{{ session($type) }}</div>
                <button type="button" class="app-toast__close" aria-label="Đóng thông báo">&times;</button>
            </div>
        @endif
    @endforeach
</div>
