document.addEventListener('DOMContentLoaded', function() {
  
  // === 1. HIỆU ỨNG HEADER (Giữ nguyên) ===
  const header = document.querySelector('.app-header');
  const videoHero = document.querySelector('.hero');
  
  function handleScroll() {
    const scrollPosition = window.scrollY;

    if (scrollPosition > 50) {
      header.classList.add('header-scrolled');
    } else {
      header.classList.remove('header-scrolled');
    }

    // === 2. HIỆU ỨNG THU NHỎ VIDEO (Giữ nguyên) ===
    const video = document.querySelector('.video-background video');
    if (video) {
      let scale = 1 - (scrollPosition / 1500);
      if (scale < 0.8) {
        scale = 0.8;
      }
      if (scale > 1) {
        scale = 1;
      }
      video.style.transform = 'scale(' + scale + ')';
    }
  }

  // === 3. HIỆU ỨNG "POP-UP" NỘI DUNG (Giữ nguyên) ===
  const sections = document.querySelectorAll('.content-section');

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target); 
        }
      });
    }, {
      root: null,
      threshold: 0.1
    });

    sections.forEach(section => {
      observer.observe(section);
    });
  } else {
    sections.forEach(section => {
      section.classList.add('is-visible');
    });
  }

  // === 4. COUNTER ANIMATION (MỚI TỪ V3.JS) ===
  function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000; // 2 giây
    const steps = 60; // 60 khung hình
    const increment = target / steps;
    let current = 0;
    
    element.textContent = '0'; // Bắt đầu từ 0

    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        element.textContent = target; // Đảm bảo kết thúc đúng số
        clearInterval(timer);
      } else {
        element.textContent = Math.floor(current);
      }
    }, duration / steps);
  }

  // Quan sát các counter
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      // Khi nó xuất hiện và chưa được đếm
      if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
        entry.target.classList.add('counted'); // Đánh dấu là đã đếm
        animateCounter(entry.target);
        counterObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 }); // Kích hoạt khi thấy 50%

  // Bắt đầu quan sát tất cả các phần tử có class .counter
  document.querySelectorAll('.counter').forEach(counter => {
    counterObserver.observe(counter);
  });
  // === KẾT THÚC PHẦN MỚI ===


  // Chạy handleScroll khi tải trang và khi cuộn
  handleScroll();
  window.addEventListener('scroll', handleScroll);

});