const toastIcons = { success: '✓', error: '!', warning: '⚠', info: 'i' };

function removeToast(toast) {
    if (!toast || toast.classList.contains('is-leaving')) return;
    toast.classList.add('is-leaving');
    window.setTimeout(() => toast.remove(), 250);
}

export function showToast(message, type = 'info', duration = 7000) {
    const container = document.getElementById('toast-container');
    if (!container || !message) return;
    const safeType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
    const toast = document.createElement('div');
    toast.className = `app-toast app-toast--${safeType}`;
    toast.setAttribute('role', safeType === 'error' ? 'alert' : 'status');
    const icon = document.createElement('span');
    icon.className = 'app-toast__icon';
    icon.textContent = toastIcons[safeType];
    const text = document.createElement('div');
    text.className = 'app-toast__message';
    text.textContent = message;
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'app-toast__close';
    close.setAttribute('aria-label', 'Đóng thông báo');
    close.innerHTML = '&times;';
    toast.append(icon, text, close);
    container.append(toast);
    close.addEventListener('click', () => removeToast(toast));
    window.setTimeout(() => removeToast(toast), duration);
}

export function friendlyApiMessage(status) {
    return ({
        401: 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.',
        403: 'Bạn không có quyền thực hiện thao tác này.',
        404: 'Không tìm thấy dữ liệu yêu cầu.',
        422: 'Thông tin nhập vào chưa hợp lệ. Vui lòng kiểm tra lại.',
        500: 'Hệ thống đang gặp sự cố. Vui lòng thử lại sau.',
    })[status] || 'Không thể thực hiện thao tác. Vui lòng thử lại.';
}

export function initUi() {
    document.querySelectorAll('.app-toast').forEach(toast => {
        const type = [...toast.classList].find(name => name.startsWith('app-toast--'))?.replace('app-toast--', '') || 'info';
        const icon = toast.querySelector('.app-toast__icon');
        if (icon && !icon.textContent) icon.textContent = toastIcons[type] || 'i';
        toast.querySelector('.app-toast__close')?.addEventListener('click', () => removeToast(toast));
        window.setTimeout(() => removeToast(toast), Number(toast.dataset.autoDismiss || 7000));
    });
}

let confirmCallback = null;
export const ConfirmModal = {
    open(options = {}) {
        const modal = document.getElementById('confirm-modal');
        if (!modal) return;
        modal.querySelector('#confirm-modal-title').textContent = options.title || 'Xác nhận thao tác';
        modal.querySelector('#confirm-modal-message').textContent = options.message || 'Bạn có chắc muốn tiếp tục?';
        const submit = modal.querySelector('#confirm-modal-submit');
        const cancel = modal.querySelector('.app-modal__actions [data-confirm-cancel]');
        submit.textContent = options.confirmText || 'Xác nhận';
        if (cancel) cancel.textContent = options.cancelText || 'Hủy';
        submit.className = `btn ${options.type === 'danger' ? 'btn-danger' : 'btn-primary'}`;
        modal.classList.toggle('app-modal--danger', options.type === 'danger');
        confirmCallback = options.onConfirm || null;
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        window.setTimeout(() => submit.focus(), 0);
    },
    close() {
        const modal = document.getElementById('confirm-modal');
        if (!modal) return;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        confirmCallback = null;
    },
};

document.addEventListener('DOMContentLoaded', () => {
    initUi();
    window.Toast = { show: showToast, friendlyApiMessage };
    window.ConfirmModal = ConfirmModal;
    document.querySelectorAll('[data-confirm-cancel]').forEach(button => button.addEventListener('click', () => ConfirmModal.close()));
    document.getElementById('confirm-modal-submit')?.addEventListener('click', () => { const callback = confirmCallback; ConfirmModal.close(); callback?.(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') ConfirmModal.close(); });
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.confirmed === '1') return;
            event.preventDefault();
            ConfirmModal.open({
                title: form.dataset.confirmTitle || 'Xác nhận thao tác',
                message: form.dataset.confirm || 'Bạn có chắc muốn tiếp tục?',
                confirmText: form.dataset.confirmText || 'Xác nhận',
                type: form.dataset.confirmType || 'danger',
                onConfirm: () => { form.dataset.confirmed = '1'; form.requestSubmit(); },
            });
        });
    });
});
