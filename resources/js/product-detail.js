function clampQuantity(input) {
    if (!input) return;
    const max = Math.max(1, Number(input.max || 1));
    const parsed = Number.parseInt(String(input.value).replace(/[^0-9]/g, ''), 10);
    input.value = String(Math.min(max, Math.max(1, Number.isFinite(parsed) ? parsed : 1)));
}
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('add-to-cart-form');
    if (!form) return;
    const input = document.getElementById('qty');
    document.getElementById('qty-minus')?.addEventListener('click', () => { input.value = Number(input.value || 1) - 1; clampQuantity(input); });
    document.getElementById('qty-plus')?.addEventListener('click', () => { input.value = Number(input.value || 1) + 1; clampQuantity(input); });
    ['input', 'change', 'blur'].forEach(name => input?.addEventListener(name, () => clampQuantity(input)));
    form.addEventListener('variant:changed', event => { input.max = String(Math.max(1, Number(event.detail?.stock || 1))); clampQuantity(input); });
});
