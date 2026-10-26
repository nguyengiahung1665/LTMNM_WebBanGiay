const toastIcons = { success: '✓', error: '!', warning: '⚠', info: 'i' };

// Product images may come from older database rows or remote URLs. If one
// cannot be loaded, replace it once with the local storefront placeholder.
document.addEventListener('error', event => {
    const image = event.target;
    if (!(image instanceof HTMLImageElement) || !image.matches('[data-product-image]')) return;
    const fallback = image.dataset.fallbackSrc;
    if (!fallback || image.src === fallback || image.dataset.fallbackApplied === '1') return;
    image.dataset.fallbackApplied = '1';
    image.src = fallback;
}, true);

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

    const menu = document.querySelector('[data-mobile-menu]');
    const backdrop = document.querySelector('.mobile-nav-backdrop');
    const menuToggle = document.querySelector('[data-mobile-menu-toggle]');
    const setMenu = open => {
        if (!menu || !backdrop || !menuToggle) return;
        menu.hidden = !open;
        backdrop.hidden = !open;
        menuToggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('nav-open', open);
    };
    menuToggle?.addEventListener('click', () => setMenu(menu.hidden));
    document.querySelectorAll('[data-mobile-menu-close]').forEach(button => button.addEventListener('click', () => setMenu(false)));

    const mobileSearch = document.querySelector('[data-mobile-search]');
    document.querySelector('[data-mobile-search-toggle]')?.addEventListener('click', () => {
        if (!mobileSearch) return;
        mobileSearch.hidden = !mobileSearch.hidden;
        if (!mobileSearch.hidden) mobileSearch.querySelector('input')?.focus();
    });

    const account = document.querySelector('.account-menu');
    account?.querySelector('.account-menu__trigger')?.addEventListener('click', event => {
        event.stopPropagation();
        const open = account.classList.toggle('is-open');
        event.currentTarget.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', event => {
        if (account && !account.contains(event.target)) {
            account.classList.remove('is-open');
            account.querySelector('.account-menu__trigger')?.setAttribute('aria-expanded', 'false');
        }
    });

    const filters = document.querySelector('[data-catalog-filters]');
    const setFilters = open => {
        filters?.classList.toggle('is-open', open);
        document.body.classList.toggle('nav-open', open);
    };
    document.querySelector('[data-filter-open]')?.addEventListener('click', () => setFilters(true));
    document.querySelector('[data-filter-close]')?.addEventListener('click', () => setFilters(false));

    document.querySelectorAll('[data-navigate-select]').forEach(select => {
        select.addEventListener('change', () => { if (select.value) window.location.assign(select.value); });
    });

    document.querySelectorAll('form[data-wishlist-form]').forEach(form => {
        form.addEventListener('submit', async event => {
            event.preventDefault();
            const button = event.submitter || form.querySelector('button');
            if (!button || button.disabled) return;
            button.disabled = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                });
                if (!response.ok) throw new Error(String(response.status));
                const data = await response.json();
                button.classList.toggle('is-active', data.wished);
                button.setAttribute('aria-pressed', String(data.wished));
                button.setAttribute('aria-label', data.wished ? 'Bỏ khỏi yêu thích' : 'Thêm vào yêu thích');
                if (!data.wished && form.dataset.removeCard === '1') {
                    form.closest('.product-card')?.remove();
                    if (!document.querySelector('.product-card')) window.location.reload();
                }
                showToast(data.wished ? 'Đã thêm vào danh sách yêu thích.' : 'Đã bỏ khỏi danh sách yêu thích.', 'success');
            } catch (error) {
                showToast(friendlyApiMessage(Number(error.message)), 'error');
            } finally {
                button.disabled = false;
            }
        });
    });
});
