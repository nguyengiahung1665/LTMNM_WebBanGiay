document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-unsaved-warning]').forEach(form => {
        const initial = new URLSearchParams(new FormData(form)).toString();
        let saved = false;
        const dirty = () => !saved && new URLSearchParams(new FormData(form)).toString() !== initial;

        form.addEventListener('submit', () => { saved = true; });

        document.querySelectorAll(`[data-cancel-form="${form.id}"]`).forEach(control => {
            control.addEventListener('click', event => {
                if (!dirty()) return;

                event.preventDefault();
                const href = control.getAttribute('href');

                ConfirmModal.open({
                    title: 'Thay đổi chưa được lưu',
                    message: 'Bạn có thay đổi chưa được lưu.\nBạn có chắc muốn thoát?',
                    confirmText: 'Thoát không lưu',
                    cancelText: 'Tiếp tục chỉnh sửa',
                    type: 'danger',
                    onConfirm: () => {
                        saved = true;
                        if (href) window.location.href = href;
                    },
                });
            });
        });

        window.addEventListener('beforeunload', event => {
            if (dirty()) {
                event.preventDefault();
                event.returnValue = '';
            }
        });
    });
});
