// ============================================================
// BudgetManager — Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

  // ── Mobile nav toggle ─────────────────────────────────────
  const toggle = document.getElementById('navToggle');
  const links  = document.getElementById('navLinks');
  if (toggle && links) {
    toggle.addEventListener('click', function () {
      links.classList.toggle('open');
    });
    document.addEventListener('click', function (e) {
      if (!toggle.contains(e.target) && !links.contains(e.target)) {
        links.classList.remove('open');
      }
    });
  }

  // ── Confirm delete ────────────────────────────────────────
  document.querySelectorAll('.confirm-delete').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      const name = this.getAttribute('data-name') || 'this record';
      if (!confirm('Are you sure you want to delete "' + name + '"?\n\nThis action cannot be undone.')) {
        e.preventDefault();
      }
    });
  });

  // ── Auto-dismiss flash messages ───────────────────────────
  const flash = document.getElementById('flashMsg');
  if (flash) {
    setTimeout(function () {
      flash.style.transition = 'opacity .5s';
      flash.style.opacity    = '0';
      setTimeout(function () { flash.remove(); }, 500);
    }, 4000);
  }

  // ── Highlight active nav link (fallback) ──────────────────
  const path = window.location.pathname;
  document.querySelectorAll('.nav-links a').forEach(function (a) {
    if (a.getAttribute('href') && path.includes(a.getAttribute('href').split('/').slice(-2, -1)[0])) {
      a.classList.add('active');
    }
  });

  // ── Form: prevent double-submit ───────────────────────────
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      const btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
      }
    });
  });

  // ── Date field default to today if empty ─────────────────
  document.querySelectorAll('input[type="date"]').forEach(function (inp) {
    if (!inp.value && !inp.readOnly) {
      inp.value = new Date().toISOString().slice(0, 10);
    }
  });

});
