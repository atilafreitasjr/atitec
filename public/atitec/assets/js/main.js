/* ATITEC — aprimoramento progressivo, JavaScript nativo. */
(() => {
  'use strict';
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const header = document.querySelector('.site-header');
  const progress = document.querySelector('.scroll-progress');
  const menuButton = document.querySelector('.menu-toggle');
  const mobileMenu = document.querySelector('#mobile-menu');
  const desktop = window.matchMedia('(min-width: 761px)');

  function setMenu(open, restoreFocus = false) {
    if (!mobileMenu || !menuButton) return;
    mobileMenu.hidden = !open;
    menuButton.setAttribute('aria-expanded', String(open));
    menuButton.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
    if (restoreFocus) menuButton.focus();
  }
  if (menuButton && mobileMenu) {
    menuButton.addEventListener('click', () => setMenu(mobileMenu.hidden));
    mobileMenu.addEventListener('click', event => {
      if (event.target.closest('a')) setMenu(false);
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && !mobileMenu.hidden) setMenu(false, true);
    });
    document.addEventListener('click', event => {
      if (!mobileMenu.hidden && header && !header.contains(event.target)) setMenu(false);
    });
    desktop.addEventListener('change', event => { if (event.matches) setMenu(false); });
  }

  let scheduled = false;
  function updateScroll() {
    const available = document.documentElement.scrollHeight - window.innerHeight;
    const ratio = available > 0 ? Math.min(1, Math.max(0, window.scrollY / available)) : 0;
    if (progress) progress.style.transform = `scaleX(${ratio})`;
    if (header) header.classList.toggle('scrolled', window.scrollY > 20);
    scheduled = false;
  }
  function requestUpdate() {
    if (!scheduled) { scheduled = true; window.requestAnimationFrame(updateScroll); }
  }
  window.addEventListener('scroll', requestUpdate, { passive: true });
  window.addEventListener('resize', requestUpdate, { passive: true });
  updateScroll();

  let observer = null;
  const reveals = Array.from(document.querySelectorAll('.reveal'));
  function makeVisible() {
    document.documentElement.classList.remove('motion-ready');
    reveals.forEach(el => el.classList.add('is-visible'));
    if (observer) observer.disconnect();
  }
  if (!reducedMotion.matches && 'IntersectionObserver' in window) {
    try {
      observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.06, rootMargin: '0px 0px 30px 0px' });
      reveals.forEach(el => observer.observe(el));
      document.documentElement.classList.add('motion-ready');
    } catch (_) { makeVisible(); }
  } else { makeVisible(); }
  reducedMotion.addEventListener('change', event => { if (event.matches) makeVisible(); });

  const filters = document.querySelector('.filters');
  const filterButtons = Array.from(document.querySelectorAll('[data-filter]'));
  const projects = Array.from(document.querySelectorAll('.project-card'));
  const filterStatus = document.querySelector('#filter-status');
  if (filters) {
    filters.hidden = false;
    filterButtons.forEach(button => button.addEventListener('click', () => {
      const selected = button.dataset.filter;
      let visibleCount = 0;
      filterButtons.forEach(item => {
        const active = item === button;
        item.classList.toggle('active', active);
        item.setAttribute('aria-pressed', String(active));
      });
      projects.forEach(project => {
        const visible = selected === 'all' || project.dataset.category === selected;
        project.hidden = !visible;
        if (visible) {
          visibleCount++;
          project.classList.add('is-visible');
        }
      });
      if (filterStatus) filterStatus.textContent = `${visibleCount} ${visibleCount === 1 ? 'projeto exibido' : 'projetos exibidos'}.`;
      requestUpdate();
    }));
  }
})();

/* Rede ambiente: movimento leve, pausa explícita e suspensão fora da tela. */
(() => {
  'use strict';
  const hero = document.querySelector('.hero');
  const canvas = document.querySelector('.network-canvas');
  const control = document.querySelector('.animation-toggle');
  if (!hero || !canvas || !control) return;
  const label = control.querySelector('.animation-label');
  if (!label) return;
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const ctx = canvas.getContext('2d');
  if (!ctx) return;
  let width = 0, height = 0, nodes = [], frame = 0, last = 0;
  let inView = true, paused = false, running = false;
  let seed = 7183;
  const random = () => { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; };
  function resize() {
    const bounds = hero.getBoundingClientRect();
    width = bounds.width; height = bounds.height;
    const scale = Math.min(window.devicePixelRatio || 1, 1.5);
    canvas.width = Math.round(width * scale); canvas.height = Math.round(height * scale);
    ctx.setTransform(scale, 0, 0, scale, 0, 0);
    seed = 7183;
    const count = width < 761 ? 30 : 52;
    nodes = Array.from({length:count}, () => ({
      x: random() * width, y: random() * height,
      vx: (random() - .5) * 24, vy: (random() - .5) * 24,
      r: 1.35 + random() * 1.75, phase: random() * Math.PI * 2
    }));
    render(0); sync();
  }
  function render(dt) {
    ctx.clearRect(0, 0, width, height);
    const distance = width < 761 ? 150 : 185;
    nodes.forEach(n => {
      n.x += n.vx * dt; n.y += n.vy * dt;
      if(n.x < -12) n.x = width + 12; if(n.x > width + 12) n.x = -12;
      if(n.y < -12) n.y = height + 12; if(n.y > height + 12) n.y = -12;
      n.phase += dt * .85;
    });
    for(let i = 0; i < nodes.length; i++) {
      const a = nodes[i];
      for(let j = i + 1; j < nodes.length; j++) {
        const b = nodes[j], d = Math.hypot(a.x - b.x, a.y - b.y);
        if(d < distance) {
          const alpha = (1 - d / distance) * .40;
          ctx.strokeStyle = `rgba(255,255,93,${alpha})`; ctx.lineWidth = 1.05;
          ctx.beginPath(); ctx.moveTo(a.x,a.y); ctx.lineTo(b.x,b.y); ctx.stroke();
          // Signal beads travel only on selected connections, with no flashing.
          if(!motion.matches && i % 9 === 0 && j % 4 === 0) {
            const t = (Math.sin(a.phase * .6) + 1) / 2;
            ctx.fillStyle = 'rgba(255,255,150,.58)';
            ctx.beginPath(); ctx.arc(a.x+(b.x-a.x)*t,a.y+(b.y-a.y)*t,2.0,0,Math.PI*2);ctx.fill();
          }
        }
      }
      ctx.fillStyle = 'rgba(243,255,190,.60)';
      ctx.beginPath(); ctx.arc(a.x,a.y,a.r,0,Math.PI*2);ctx.fill();
    }
  }
  function tick(time) {
    if(!running) return;
    frame = requestAnimationFrame(tick);
    if(time - last < 33) return; // At most approximately 30 fps.
    const dt = last ? Math.min((time-last)/1000,.06) : 0;
    last = time; render(dt);
  }
  function sync() {
    const shouldRun = !paused && !motion.matches && inView && !document.hidden;
    hero.classList.toggle('is-paused',paused || motion.matches);
    hero.classList.toggle('ambient-suspended',!inView || document.hidden);
    if(shouldRun && !running) { running = true; last = 0; frame = requestAnimationFrame(tick); }
    if(!shouldRun && running) { running = false; cancelAnimationFrame(frame); }
    control.hidden = false;
    control.disabled = motion.matches;
    control.setAttribute('aria-pressed',String(paused || motion.matches));
    label.textContent = motion.matches ? 'Movimento reduzido' : paused ? 'Retomar animação' : 'Pausar animação';
    control.setAttribute('aria-label',motion.matches ? 'Movimento reduzido conforme preferência do dispositivo' : paused ? 'Retomar animação da abertura' : 'Pausar animação da abertura');
  }
  control.addEventListener('click', () => { paused = !paused; sync(); });
  motion.addEventListener('change',sync);
  document.addEventListener('visibilitychange',sync);
  if('IntersectionObserver' in window) {
    const visibility = new IntersectionObserver(entries => {
      inView = entries[0].isIntersecting; sync();
    },{threshold:0});
    visibility.observe(hero);
  }
  if('ResizeObserver' in window) {
    const observer = new ResizeObserver(resize);observer.observe(hero);
  } else { window.addEventListener('resize',resize,{passive:true}); }
  resize();
})();
