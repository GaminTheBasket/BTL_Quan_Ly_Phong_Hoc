document.addEventListener('DOMContentLoaded', function(){
  const linesEl = document.getElementById('quoteLines');
  const subEl = document.getElementById('quoteSub');
  if(!linesEl) return;

  // Hai dòng câu quote (dịch sang tiếng Việt) để giữ bố cục cân đối
  const QUOTES = [
    { l1: 'Quản lý Thông minh.',        l2: 'Học tập Thông minh hơn.',    sub: 'Quản lý phòng học, lịch và việc sử dụng tại một nơi.' },
    { l1: 'Phòng đúng, thời gian đúng.', l2: 'Hiệu quả tốt hơn.',          sub: 'Trạng thái thời gian thực giúp mọi lớp học đúng tiến độ.' },
    { l1: 'Lập kế hoạch tự tin.',       l2: 'Dạy học nhẹ nhàng.',        sub: 'Tạo lịch, theo dõi phòng và giữ đúng giờ.' }
  ];
  let idx = 0;
  function render(){
    const q = QUOTES[idx];
    const spans = linesEl.querySelectorAll('span');
    if(spans[0]) spans[0].textContent = q.l1;
    if(spans[1]) spans[1].textContent = q.l2;
    if(subEl) subEl.textContent = q.sub;
    idx = (idx + 1) % QUOTES.length;
  }
  render();
  setInterval(render, 5000);

  const bgVideo = document.querySelector('.video-background video');
  if (bgVideo) {
    // đảm bảo metadata đã load rồi set tốc độ (nhiều trình duyệt chấp nhận set ngay)
    bgVideo.addEventListener('loadedmetadata', () => { bgVideo.playbackRate = 1.5; });
    bgVideo.playbackRate = 1.5;
  }
});
