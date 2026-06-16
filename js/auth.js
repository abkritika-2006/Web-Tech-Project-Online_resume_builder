/* Client-side validation for the register form */
const form = document.querySelector('form');
if (form) {
  form.addEventListener('submit', (e) => {
    const password = form.querySelector('#password')?.value || '';
    const confirm  = form.querySelector('#confirm')?.value  || '';

    if (password && confirm && password !== confirm) {
      e.preventDefault();
      // Show inline error
      let err = document.querySelector('.alert-error');
      if (!err) {
        err = document.createElement('div');
        err.className = 'alert alert-error';
        form.insertAdjacentElement('beforebegin', err);
      }
      err.textContent = 'Passwords do not match.';
      err.style.display = 'flex';
    }
  });
}
