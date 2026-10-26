function clampQuantity(input) {
    if (!input) return;
    const max = Math.max(1, Number(input.max || 1));
    const parsed = Number.parseInt(String(input.value).replace(/[^0-9]/g, ''), 10);
    input.value = String(Math.min(max, Math.max(1, Number.isFinite(parsed) ? parsed : 1)));
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-gallery-thumb]').forEach(thumb => {
        thumb.addEventListener('click', () => {
            const main = document.getElementById('main-product-image');
            if (!main) return;
            main.src = thumb.dataset.src;
            main.alt = thumb.dataset.alt || main.alt;
            document.querySelectorAll('[data-gallery-thumb]').forEach(item => item.classList.toggle('is-active', item === thumb));
        });
    });

    const form = document.getElementById('add-to-cart-form');
    if (!form) return;

    const input = document.getElementById('qty');
    document.getElementById('qty-minus')?.addEventListener('click', () => {
        input.value = Number(input.value || 1) - 1;
        clampQuantity(input);
    });
    document.getElementById('qty-plus')?.addEventListener('click', () => {
        input.value = Number(input.value || 1) + 1;
        clampQuantity(input);
    });
    ['input', 'change', 'blur'].forEach(name => input?.addEventListener(name, () => clampQuantity(input)));

    let variants = [];
    try { variants = JSON.parse(form.dataset.variants || '[]'); } catch { variants = []; }
    const hasSizes = variants.some(variant => variant.size);
    const hasColors = variants.some(variant => variant.color_name);
    let selectedSize = null;
    let selectedColor = null;
    const colorKey = (name, code) => `${name || ''}|${code || ''}`;
    const matches = (size, color) => variants.filter(variant =>
        (!hasSizes || size === null || variant.size === size)
        && (!hasColors || color === null || colorKey(variant.color_name, variant.color_code) === color)
    );

    const refresh = () => {
        document.querySelectorAll('[data-size]').forEach(button => {
            const stock = matches(button.dataset.size, selectedColor).reduce((sum, variant) => sum + Number(variant.stock), 0);
            button.disabled = stock <= 0;
            button.classList.toggle('active', button.dataset.size === selectedSize);
        });
        document.querySelectorAll('[data-color-name]').forEach(button => {
            const key = colorKey(button.dataset.colorName, button.dataset.colorCode);
            const stock = matches(selectedSize, key).reduce((sum, variant) => sum + Number(variant.stock), 0);
            button.disabled = stock <= 0;
            button.classList.toggle('active', key === selectedColor);
        });

        const available = matches(selectedSize, selectedColor);
        const variant = available.length === 1 ? available[0] : null;
        const hidden = document.getElementById('selected-variant');
        const stockNote = document.getElementById('variant-stock-note');
        if (variant && Number(variant.stock) > 0) {
            hidden.value = variant.id;
            input.max = String(variant.stock);
            clampQuantity(input);
            stockNote.textContent = `Còn ${variant.stock} sản phẩm`;
        } else {
            hidden.value = '';
            stockNote.textContent = (selectedSize || selectedColor) ? 'Hãy chọn đủ size và màu.' : 'Chọn phiên bản phù hợp để xem tồn kho.';
        }
    };

    document.querySelectorAll('[data-size]').forEach(button => button.addEventListener('click', () => {
        if (!button.disabled) { selectedSize = button.dataset.size; refresh(); }
    }));
    document.querySelectorAll('[data-color-name]').forEach(button => button.addEventListener('click', () => {
        if (!button.disabled) { selectedColor = colorKey(button.dataset.colorName, button.dataset.colorCode); refresh(); }
    }));

    if (!hasSizes && !hasColors && variants.length === 1) {
        document.getElementById('selected-variant').value = variants[0].id;
        input.max = String(Math.max(1, variants[0].stock));
    }
    refresh();

    form.addEventListener('submit', event => {
        if (form.dataset.authenticated !== '1') {
            event.preventDefault();
            window.Toast?.show('Vui lòng đăng nhập để thêm sản phẩm vào giỏ.', 'warning');
            window.setTimeout(() => window.location.assign(form.dataset.loginUrl), 450);
            return;
        }
        if ((hasSizes || hasColors) && !document.getElementById('selected-variant').value) {
            event.preventDefault();
            window.Toast?.show('Vui lòng chọn size và màu còn hàng.', 'warning');
        }
    });
});
