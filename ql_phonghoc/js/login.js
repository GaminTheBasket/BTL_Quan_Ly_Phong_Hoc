document.addEventListener('DOMContentLoaded', function() {
  const toggleBtn = document.getElementById('togglePassword');
  const passInput = document.getElementById('passwordInput');

  if (toggleBtn && passInput) {
    toggleBtn.addEventListener('click', function() {
      // Toggle the type attribute
      const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passInput.setAttribute('type', type);
      
      // Toggle the 'show' class on the button
      this.classList.toggle('show');
      
      // Toggle the aria-label for accessibility
      if (type === 'text') {
        this.setAttribute('aria-label', 'Ẩn mật khẩu');
      } else {
        this.setAttribute('aria-label', 'Hiện mật khẩu');
      }
    });
  }
});