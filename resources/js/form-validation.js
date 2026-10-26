document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        const validate = field => {
            const feedback = form.querySelector(`[data-error-for="${field.name}"]`);
            let message = '';
            if (field.required && !field.value.trim()) message = field.dataset.requiredMessage || 'Vui lòng nhập thông tin này.';
            if (!message && field.type === 'email' && field.value && field.validity.typeMismatch) message = field.dataset.emailMessage || 'Email không hợp lệ.';
            if (!message && field.name === 'new_password_confirmation' && field.value !== form.elements.new_password?.value) message = 'Xác nhận mật khẩu không khớp.';
            if (!message && field.name === 'password_confirmation' && field.value !== form.elements.password?.value) message = 'Xác nhận mật khẩu không khớp.';
            field.classList.toggle('is-invalid', Boolean(message)); field.setAttribute('aria-invalid', message ? 'true' : 'false');
            if (feedback) { feedback.textContent = message; feedback.hidden = !message; }
            return !message;
        };
        form.addEventListener('submit', event => { const fields = [...form.querySelectorAll('input,select,textarea')].filter(field => field.name); const isValid = fields.map(validate).every(Boolean); if (!isValid) { event.preventDefault(); fields.find(field => field.classList.contains('is-invalid'))?.focus(); } });
        form.addEventListener('input', event => event.target.name && validate(event.target));
        form.addEventListener('change', event => event.target.name && validate(event.target));
    });
});
