const forms = document.querySelectorAll('.form-prevent-multi-submit') || [];
forms.forEach(form => form.onsubmit = () => {
    const buttons = document.querySelectorAll('.btn-prevent-multi-submit');
    buttons.forEach(button => button.disabled = true);
});