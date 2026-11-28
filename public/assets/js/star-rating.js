// Lucian UX/UI Prompt #016 · MIT License (c) 2026 Seokwoooo · https://lucianlabs.dev/uxui-prompt/#016
(function () {
  var STAR = 'M26 4.5l6.4 13.2 14.4 2-10.5 10.1 2.6 14.3L26 37.3l-12.9 6.8 2.6-14.3L5.2 19.7l14.4-2z';
  var WORDS = ['Chưa chấm', 'Rất tệ', 'Chưa tốt', 'Ổn', 'Khá ổn', 'Xuất sắc'];
  var COLORS = ['text-[#64748b]', 'text-[#dc2626]', 'text-[#ea580c]', 'text-[#d97706]', 'text-[#16a34a]', 'text-[#6a57f2]'];
  var rm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function makeStar(value, gradientId) {
    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'u016-s relative size-10 shrink-0 border-0 bg-transparent p-0 transition-transform duration-300 [transition-timing-function:cubic-bezier(.34,1.45,.64,1)] hover:scale-[1.12] hover:rotate-[-6deg] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#6a57f2] motion-reduce:transition-none';
    button.dataset.star = String(value);
    button.setAttribute('aria-label', value + ' sao');
    button.innerHTML = '<svg class="pointer-events-none absolute inset-0 size-full overflow-visible" viewBox="0 0 52 52" aria-hidden="true"><path class="fill-[#eae7e1] stroke-[#b8b3ab] stroke-1" d="' + STAR + '" stroke-linejoin="round"></path><path class="u016-fg origin-[50%_55%] scale-0 fill-[#f59e0b] opacity-0 transition-[transform,opacity] duration-300 [transition-timing-function:cubic-bezier(.34,1.45,.64,1)] motion-reduce:transition-none" d="' + STAR + '" fill="url(#' + gradientId + ')" stroke-linejoin="round"></path></svg>';
    for (var index = 0; index < 5; index += 1) {
      var spark = document.createElement('span');
      spark.className = 'pointer-events-none absolute left-1/2 top-1/2 size-1.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#ffb547] opacity-0';
      spark.dataset.sparkAngle = String(index * 72);
      button.appendChild(spark);
    }
    return button;
  }

  function animatePop(star) {
    if (rm) return;
    star.animate([
      { transform: 'scale(1) rotate(0deg)' },
      { transform: 'scale(1.32) rotate(12deg)' },
      { transform: 'scale(1) rotate(0deg)' }
    ], { duration: 550, easing: 'cubic-bezier(.34,1.45,.64,1)' });
    star.querySelectorAll('[data-spark-angle]').forEach(function (spark) {
      var angle = spark.dataset.sparkAngle;
      spark.animate([
        { opacity: 1, transform: 'translate(-50%,-50%) rotate(' + angle + 'deg) translateY(-14px) scale(1)' },
        { opacity: 0, transform: 'translate(-50%,-50%) rotate(' + angle + 'deg) translateY(-40px) scale(.3)' }
      ], { duration: 600, easing: 'cubic-bezier(.2,.8,.2,1)', fill: 'forwards' });
    });
  }

  function init(root) {
    var select = root;
    var label = select.closest('label');
    if (!label || label.dataset.starRatingReady === 'true') return;
    label.dataset.starRatingReady = 'true';
    select.classList.add('sr-only');
    select.setAttribute('data-star-rating-value', 'true');

    var rating = Math.max(1, Math.min(5, Number(select.value) || 5));
    var gradientId = 'u016-g-' + Math.random().toString(36).slice(2, 8);
    var widget = document.createElement('span');
    widget.className = 'u016 mt-2 flex w-fit flex-col items-start gap-2 rounded-2xl';
    widget.dataset.starRating = 'true';
    widget.innerHTML = '<span class="u016-stars flex rounded-2xl" role="slider" tabindex="0" aria-label="Mức đánh giá" aria-valuemin="1" aria-valuemax="5"></span><span class="flex h-10 items-baseline gap-2.5" aria-hidden="true"><span class="text-[27px] font-extrabold tabular-nums tracking-[-.04em] transition-colors duration-300" data-star-score></span><span class="h-6 w-[132px] overflow-hidden text-base font-bold text-[#64748b]" data-star-word></span></span><svg class="absolute size-0" aria-hidden="true"><defs><linearGradient id="' + gradientId + '" x1="0" y1="0" x2=".6" y2="1"><stop offset="0" stop-color="#ffd66b"></stop><stop offset=".6" stop-color="#ffaa2c"></stop><stop offset="1" stop-color="#ff7a45"></stop></linearGradient></defs></svg>';
    select.insertAdjacentElement('afterend', widget);

    var wrap = widget.querySelector('.u016-stars');
    var score = widget.querySelector('[data-star-score]');
    var word = widget.querySelector('[data-star-word]');
    var stars = [];
    for (var index = 1; index <= 5; index += 1) {
      var star = makeStar(index, gradientId);
      wrap.appendChild(star);
      stars.push(star);
    }

    function update(n, pop) {
      rating = Math.max(1, Math.min(5, n));
      select.value = String(rating);
      score.textContent = rating.toFixed(1);
      score.className = 'text-[27px] font-extrabold tabular-nums tracking-[-.04em] transition-colors duration-300 ' + COLORS[rating];
      word.textContent = WORDS[rating];
      wrap.setAttribute('aria-valuenow', String(rating));
      wrap.setAttribute('aria-valuetext', rating + ' sao, ' + WORDS[rating]);
      stars.forEach(function (star, index) {
        var active = index < rating;
        var fg = star.querySelector('.u016-fg');
        fg.classList.toggle('scale-100', active);
        fg.classList.toggle('opacity-100', active);
        fg.classList.toggle('scale-0', !active);
        fg.classList.toggle('opacity-0', !active);
        if (active && pop) window.setTimeout(function () { animatePop(star); }, index * 70);
      });
    }

    function preview(n) {
      stars.forEach(function (star, index) {
        var fg = star.querySelector('.u016-fg');
        var previewing = index < n && index >= rating;
        fg.classList.toggle('scale-[.72]', previewing);
        fg.classList.toggle('opacity-55', previewing);
      });
    }

    stars.forEach(function (star) {
      star.addEventListener('pointerenter', function () { preview(Number(star.dataset.star)); });
      star.addEventListener('click', function () { update(Number(star.dataset.star), true); });
    });
    wrap.addEventListener('pointerleave', function () { preview(0); });
    wrap.addEventListener('keydown', function (event) {
      var delta = { ArrowRight: 1, ArrowUp: 1, ArrowLeft: -1, ArrowDown: -1 }[event.key];
      if (event.key === 'Home') { event.preventDefault(); update(1, false); }
      if (event.key === 'End') { event.preventDefault(); update(5, false); }
      if (delta) { event.preventDefault(); update(Math.max(1, Math.min(5, rating + delta)), true); }
      if (/^[1-5]$/.test(event.key)) update(Number(event.key), true);
    });
    select.addEventListener('change', function () { update(Number(select.value), false); });
    update(rating, false);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select[name="rating"]').forEach(init);
  });
})();
