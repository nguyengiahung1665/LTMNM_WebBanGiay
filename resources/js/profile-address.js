document.addEventListener('DOMContentLoaded', () => {
    const province = document.getElementById('province_code');
    const ward = document.getElementById('ward_code');
    const node = document.getElementById('vietnam-addresses-data');
    if (!province || !ward || !node) return;
    let locations = []; try { locations = JSON.parse(node.textContent); } catch { return; }
    const selectedProvince = province.dataset.selected || '';
    const renderWards = (code, selected = '') => {
        const current = locations.find(item => String(item.code) === String(code));
        ward.replaceChildren(new Option('Chọn phường/xã', ''));
        (current?.wards || []).forEach(item => ward.add(new Option(item.name, item.code, false, String(item.code) === String(selected))));
        ward.disabled = !current;
    };
    locations.forEach(item => province.add(new Option(item.name, item.code, false, String(item.code) === String(selectedProvince))));
    renderWards(selectedProvince, ward.dataset.selected || '');
    province.addEventListener('change', () => renderWards(province.value));
});
