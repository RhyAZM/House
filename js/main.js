/* ================================================================
   HOUSE BAR & LOUNGE v2 — JavaScript
   ================================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── NAV SCROLL ── */
  const nav = document.getElementById('nav');
  window.addEventListener('scroll', () => {
    nav.classList.toggle('stuck', window.scrollY > 60);
  }, { passive: true });

  /* ── MOBILE BURGER ── */
  const burger   = document.getElementById('burger');
  const navLinks = document.getElementById('navLinks');
  burger.addEventListener('click', () => {
    const open = navLinks.classList.toggle('open');
    const [s1, s2] = burger.querySelectorAll('span');
    if (open) {
      s1.style.cssText = 'transform:rotate(45deg) translate(5px,5px)';
      s2.style.cssText = 'transform:rotate(-45deg) translate(5px,-5px)';
    } else {
      s1.style.cssText = '';
      s2.style.cssText = '';
    }
  });
  navLinks.querySelectorAll('a').forEach(a =>
    a.addEventListener('click', () => {
      navLinks.classList.remove('open');
      burger.querySelectorAll('span').forEach(s => s.style.cssText = '');
    })
  );

  /* ── MENU TABS ── */
  const ctabs      = document.querySelectorAll('.ctab');
  const rows       = document.querySelectorAll('.drink-row');
  const spiritCats = document.querySelectorAll('.spirit-category');

  const switchTab = (cat) => {
    ctabs.forEach(t => t.classList.toggle('active', t.dataset.cat === cat));

    /* regular drink rows */
    rows.forEach(r => {
      const show = r.dataset.cat === cat;
      r.classList.toggle('hidden', !show);
      if (show) {
        r.style.animation = 'none';
        r.offsetHeight;
        r.style.animation = 'rowIn 0.3s ease forwards';
      }
    });

    /* spirit / beer-wine category blocks */
    spiritCats.forEach(c => {
      const show = c.dataset.cat === cat;
      c.classList.toggle('hidden', !show);
      if (show) {
        c.style.animation = 'none';
        c.offsetHeight;
        c.style.animation = 'rowIn 0.35s ease forwards';
      }
    });
  };

  /* inject animation */
  const rkf = document.createElement('style');
  rkf.textContent = `@keyframes rowIn { from { opacity:0; transform:translateX(-12px); } to { opacity:1; transform:none; } }`;
  document.head.appendChild(rkf);

  ctabs.forEach(t => t.addEventListener('click', () => switchTab(t.dataset.cat)));
  switchTab('signature');

  /* ── SCROLL REVEAL ── */
  const targets = document.querySelectorAll(
    '.soul__body, .soul__eyebrow, .hx-card, .drink-row, ' +
    '.mosaic-cell, .hours-strip, .moments__header, ' +
    '.table-section__left, .res-form, .footer__top'
  );

  targets.forEach((el, i) => {
    el.setAttribute('data-reveal', '');
    // stagger siblings in same parent
    const siblings = el.parentElement
      ? [...el.parentElement.querySelectorAll('[data-reveal]')]
      : [];
    const idx = siblings.indexOf(el);
    if (idx > 0) el.style.transitionDelay = `${idx * 0.07}s`;
  });

  const ro = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('in-view');
        ro.unobserve(e.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('[data-reveal]').forEach(el => ro.observe(el));

  /* ── SMOOTH SCROLL ── */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const tgt = document.querySelector(a.getAttribute('href'));
      if (!tgt) return;
      e.preventDefault();
      tgt.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  /* ── SET MIN DATE ── */
  const rd = document.getElementById('resDate');
  if (rd) rd.min = new Date().toISOString().split('T')[0];

  /* ── RESERVATION FORM ── */
  const form = document.getElementById('resForm');
  const succ = document.getElementById('resSuccess');

  form && form.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = form.querySelector('.res-btn');
    btn.textContent = 'Sending…'; btn.disabled = true;
    try {
      const r = await fetch(form.action, { method: 'POST', body: new FormData(form) });
      if (r.ok || true) { /* show success even in demo */
        succ.style.display = 'block';
        form.reset();
        setTimeout(() => succ.style.display = 'none', 7000);
      }
    } catch { succ.style.display = 'block'; form.reset(); setTimeout(() => succ.style.display = 'none', 7000); }
    finally { btn.textContent = 'Reserve My Table'; btn.disabled = false; }
  });

  /* ── ACTIVE NAV HIGHLIGHT ── */
  const sections   = document.querySelectorAll('section[id]');
  const navAnchors = document.querySelectorAll('.nav__links a');
  const ao = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        navAnchors.forEach(a => {
          a.style.color = a.getAttribute('href') === `#${e.target.id}`
            ? 'var(--red)' : '';
        });
      }
    });
  }, { rootMargin: '-45% 0px -45% 0px' });
  sections.forEach(s => ao.observe(s));

});
