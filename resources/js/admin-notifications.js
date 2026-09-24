const STORAGE_KEY = 'admin_order_notifications_v1';
const MAX_ITEMS = 30;

function readItems() { try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); } catch { return []; } }
function saveItems(items) { localStorage.setItem(STORAGE_KEY, JSON.stringify(items.slice(0, MAX_ITEMS))); }

export function initAdminNotifications(echo) {
    const root = document.getElementById('admin-notifications');
    if (!root || root.dataset.ready === '1') return;
    root.dataset.ready = '1';
    const bell = document.getElementById('notification-bell');
    const panel = document.getElementById('notification-panel');
    const list = document.getElementById('notification-list');
    const empty = document.getElementById('notification-empty');
    const badge = document.getElementById('notification-count');
    let items = readItems();
    const render = () => {
        list.replaceChildren();
        empty.hidden = items.length > 0;
        const unread = items.filter(item => !item.read).length;
        badge.textContent = unread > 99 ? '99+' : String(unread);
        badge.hidden = unread === 0;
        items.forEach(item => {
            const link = document.createElement('a');
            link.href = `/admin/don-hang/${item.id}`;
            link.className = `notification-item${item.read ? '' : ' is-unread'}`;
            const icon = document.createElement('span'); icon.className = 'notification-item__icon'; icon.textContent = '📦';
            const body = document.createElement('span');
            const name = document.createElement('strong'); name.textContent = item.full_name;
            const order = document.createElement('small'); order.textContent = `Đơn hàng #${item.order_code}`;
            const time = document.createElement('time'); time.textContent = item.created_at;
            body.append(name, order, time); link.append(icon, body);
            link.addEventListener('click', () => { item.read = true; saveItems(items); });
            list.append(link);
        });
    };
    bell.addEventListener('click', event => { event.stopPropagation(); panel.hidden = !panel.hidden; bell.setAttribute('aria-expanded', String(!panel.hidden)); });
    document.addEventListener('click', event => { if (!root.contains(event.target)) { panel.hidden = true; bell.setAttribute('aria-expanded', 'false'); } });
    document.getElementById('notifications-mark-read')?.addEventListener('click', () => { items = items.map(item => ({ ...item, read: true })); saveItems(items); render(); });
    render();
    echo?.private('admin.orders').listen('.order.placed', event => {
        items = [{ id: event.id, order_code: event.order_code, full_name: event.full_name, created_at: event.created_at, read: false }, ...items.filter(item => item.id !== event.id)];
        saveItems(items); render();
        bell.classList.remove('is-ringing'); void bell.offsetWidth; bell.classList.add('is-ringing');
        window.Toast?.show(`${event.full_name} vừa đặt đơn #${event.order_code}`, 'info');
    });
}
