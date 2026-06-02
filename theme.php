<!-- StudyGenie Design System v2 — Sleek Dark Dashboard -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════
   DESIGN TOKENS  (dark)
═══════════════════════════════════════════ */
:root {
  /* surfaces */
  --bg:           #0a0e14;
  --surface:      #121823;
  --surface-2:    #18202c;
  --surface-3:    #1f2835;
  --border:       rgba(255,255,255,.08);
  --border-2:     rgba(255,255,255,.14);

  /* text */
  --text:         #e7edf4;
  --text-muted:   #8b95a7;
  --text-dim:     #5f6b7d;

  /* brand / accent */
  --accent:       #3fb950;
  --accent-hover: #4ad05c;
  --accent-bright:#00e676;
  --accent-soft:  rgba(63,185,80,.14);
  --accent-line:  rgba(63,185,80,.35);

  /* status */
  --danger:  #f85149;
  --warn:    #d29922;
  --info:    #58a6ff;

  /* legacy aliases (pages reference these — remapped to dark) */
  --glass-bg:     var(--surface);
  --glass-border: var(--border);
  --glass-shadow: 0 1px 2px rgba(0,0,0,.4), 0 8px 24px rgba(0,0,0,.25);
  --g1:#1b5e20; --g2:#4ad05c; --g3:#3fb950; --g4:#43a047; --g5:#66bb6a;

  --radius:    14px;
  --radius-sm: 9px;
  --transition: .18s cubic-bezier(.4,0,.2,1);
}

*,*::before,*::after { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior: smooth; }

body {
  font-family: 'Inter', system-ui, sans-serif;
  min-height: 100vh;
  background: var(--bg);
  color: var(--text);
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
  font-feature-settings: "cv02","cv03","cv04";
}

/* Calm background — single subtle glow, no animation/particles */
body::before {
  content:''; position: fixed; inset: 0; z-index: -1; pointer-events: none;
  background:
    radial-gradient(900px 500px at 18% -8%, rgba(63,185,80,.07), transparent 60%),
    radial-gradient(700px 500px at 100% 0%, rgba(0,230,118,.04), transparent 55%);
}

::selection { background: rgba(63,185,80,.28); }

/* scrollbars */
::-webkit-scrollbar { width: 10px; height: 10px; }
::-webkit-scrollbar-thumb { background: #232d3a; border-radius: 6px; border: 2px solid var(--bg); }
::-webkit-scrollbar-thumb:hover { background: #2c3744; }

/* ═══════════════════════════════════════════
   PAGE WRAPPER
═══════════════════════════════════════════ */
.page-wrapper { animation: pageIn .35s ease both; padding: 22px; }
@keyframes pageIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ═══════════════════════════════════════════
   TOPBAR
═══════════════════════════════════════════ */
.sg-header {
  background: rgba(18,24,35,.72);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid var(--border);
  padding: 13px 22px;
  border-radius: var(--radius);
  display: flex; align-items: center; gap: 14px;
  margin-bottom: 22px;
  position: sticky; top: 12px; z-index: 100;
}
.sg-logo {
  font-size: 18px; font-weight: 800; letter-spacing: -.4px;
  color: var(--text); user-select: none;
}
.topbar-title { font-size: 14px; font-weight: 700; color: var(--text); flex: 1; letter-spacing:-.2px; }
.topbar-user { display: flex; align-items: center; gap: 10px; }
.topbar-user .tb-name { font-size: 13px; font-weight: 600; color: var(--text-muted); }
.topbar-user .tb-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), var(--accent-bright));
  color: #06140a; font-weight: 800; font-size: 14px;
  display: flex; align-items: center; justify-content: center;
}

/* ═══════════════════════════════════════════
   CARDS
═══════════════════════════════════════════ */
.sg-card, .sg-card-flat {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--glass-shadow);
  padding: 24px;
}
.sg-card { transition: border-color var(--transition), transform var(--transition); }
.sg-card:hover { border-color: var(--border-2); transform: translateY(-2px); }

/* ═══════════════════════════════════════════
   BUTTONS
═══════════════════════════════════════════ */
.sg-btn {
  position: relative; overflow: hidden;
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 10px 18px; border: 1px solid transparent; border-radius: var(--radius-sm);
  font-family: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer;
  text-decoration: none; white-space: nowrap;
  transition: background var(--transition), border-color var(--transition), transform var(--transition);
}
.sg-btn:active { transform: scale(.98); }

.sg-btn-primary {
  background: var(--accent); color: #06140a; font-weight: 700;
}
.sg-btn-primary:hover { background: var(--accent-hover); }

.sg-btn-ghost {
  background: var(--surface-2); color: var(--text); border-color: var(--border);
}
.sg-btn-ghost:hover { background: var(--surface-3); border-color: var(--border-2); }

.sg-btn-danger {
  background: rgba(248,81,73,.12); color: #ff7b72; border-color: rgba(248,81,73,.3);
}
.sg-btn-danger:hover { background: rgba(248,81,73,.2); }

/* ripple */
.ripple { position: absolute; border-radius: 50%; background: rgba(255,255,255,.18);
  transform: scale(0); animation: rippleAnim .55s linear; pointer-events: none; }
@keyframes rippleAnim { to { transform: scale(4); opacity: 0; } }

/* ═══════════════════════════════════════════
   INPUTS
═══════════════════════════════════════════ */
.sg-input {
  width: 100%; padding: 12px 14px;
  border: 1px solid var(--border); border-radius: var(--radius-sm);
  font-family: inherit; font-size: 14px;
  background: var(--surface-2); color: var(--text);
  transition: border-color var(--transition), box-shadow var(--transition);
  outline: none;
}
.sg-input::placeholder { color: var(--text-dim); }
.sg-input:focus { border-color: var(--accent-line); box-shadow: 0 0 0 3px var(--accent-soft); }

/* ═══════════════════════════════════════════
   TOASTS
═══════════════════════════════════════════ */
#toast-container { position: fixed; bottom: 26px; right: 26px; z-index: 9999;
  display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
.toast {
  pointer-events: all; padding: 13px 16px; border-radius: 10px;
  font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 10px;
  max-width: 340px; cursor: pointer; border: 1px solid var(--border-2);
  background: var(--surface-3); color: var(--text);
  box-shadow: 0 12px 32px rgba(0,0,0,.45); animation: toastIn .28s ease both;
}
.toast.removing { animation: toastOut .28s ease forwards; }
.toast-success { border-left: 3px solid var(--accent); }
.toast-error   { border-left: 3px solid var(--danger); }
.toast-info    { border-left: 3px solid var(--info); }
@keyframes toastIn  { from { opacity:0; transform: translateX(40px); } to { opacity:1; transform: none; } }
@keyframes toastOut { to { opacity:0; transform: translateX(40px); } }

/* ═══════════════════════════════════════════
   SKELETON
═══════════════════════════════════════════ */
.skeleton {
  background: linear-gradient(90deg, rgba(255,255,255,.04) 25%, rgba(255,255,255,.09) 50%, rgba(255,255,255,.04) 75%);
  background-size: 400% 100%; animation: shimmer 1.4s ease infinite; border-radius: 8px;
}
@keyframes shimmer { to { background-position: -400% 0; } }

/* ═══════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════ */
.sidenav {
  height: 100%; width: 0; position: fixed; z-index: 200; top: 0; left: 0;
  background: #0c111a; border-right: 1px solid var(--border);
  overflow: hidden; transition: width .3s cubic-bezier(.4,0,.2,1);
  display: flex; flex-direction: column;
}
.sidenav .nav-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 20px; min-width: 250px; border-bottom: 1px solid var(--border);
}
.sidenav .nav-brand {
  font-size: 18px; font-weight: 800; white-space: nowrap; letter-spacing: -.3px;
  display: flex; align-items: center; gap: 9px; color: var(--text);
}
.sidenav .nav-brand svg { color: var(--accent); }
.sidenav .nav-links { flex: 1; padding: 14px 12px; min-width: 250px; overflow-y: auto; }
.sidenav .nav-section {
  font-size: 10.5px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase;
  color: var(--text-dim); padding: 8px 12px 6px;
}
.sidenav a {
  padding: 9px 12px; margin-bottom: 2px; text-decoration: none;
  font-size: 14px; font-weight: 500; color: var(--text-muted);
  display: flex; align-items: center; gap: 11px;
  border-radius: 8px; transition: all .15s; white-space: nowrap;
}
.sidenav a svg { width: 18px; height: 18px; flex-shrink: 0; stroke-width: 2; }
.sidenav a:hover { color: var(--text); background: var(--surface-2); }
.sidenav a.active { color: var(--accent); background: var(--accent-soft); font-weight: 600; }
.sidenav .nav-footer { padding: 12px; min-width: 250px; border-top: 1px solid var(--border); }
.sidenav .nav-logout { color: #ff7b72; }
.sidenav .nav-logout:hover { color: #ff9d96; background: rgba(248,81,73,.1); }
.sidenav .closebtn {
  font-size: 22px; color: var(--text-muted); cursor: pointer; background: none;
  border: none; font-family: inherit; line-height: 1;
}
.sidenav .closebtn:hover { color: var(--text); }

.ham-btn {
  background: none; border: none; cursor: pointer; color: var(--text-muted);
  display: inline-flex; align-items: center; padding: 0 4px 0 0;
}
.ham-btn:hover { color: var(--text); }

#overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.6);
  display: none; z-index: 150; transition: opacity .3s;
}

/* ═══════════════════════════════════════════
   DESKTOP APP-SHELL (persistent sidebar)
═══════════════════════════════════════════ */
@media (min-width: 1024px) {
  .sidenav { width: 250px !important; }
  .sidenav .closebtn { display: none; }
  #overlay { display: none !important; }
  .ham-btn { display: none !important; }
  body:has(.sidenav) .page-wrapper {
    margin-left: 250px !important; max-width: none !important;
    width: auto !important; padding: 26px 40px !important;
  }
}
@media (min-width: 1600px) {
  body:has(.sidenav) .page-wrapper { padding: 30px 56px !important; }
}

/* ═══════════════════════════════════════════
   BADGES + UTILITIES
═══════════════════════════════════════════ */
.badge { display: inline-block; padding: 4px 11px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-green   { background: var(--accent-soft);          color: var(--accent); }
.badge-yellow  { background: rgba(210,153,34,.15);        color: #e3b341; }
.badge-red     { background: rgba(248,81,73,.13);         color: #ff7b72; }
.badge-blue    { background: rgba(88,166,255,.13);        color: #79b8ff; }

.section-title { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 16px; letter-spacing: -.3px; }
.text-muted { color: var(--text-muted); }
.text-green { color: var(--accent); }

a { color: inherit; text-decoration: none; }
</style>

<div id="toast-container"></div>

<script>
/* ── Ripple ───────────────────────────── */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.sg-btn');
  if (!btn) return;
  const r = document.createElement('span');
  r.className = 'ripple';
  const rect = btn.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  r.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px`;
  btn.appendChild(r);
  setTimeout(() => r.remove(), 600);
});

/* ── Toasts ───────────────────────────── */
function showToast(msg, type = 'info', duration = 3500) {
  const icons = { success: '✓', error: '✕', info: 'ℹ' };
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span>${icons[type]||'ℹ'}</span><span>${msg}</span>`;
  t.onclick = () => removeToast(t);
  document.getElementById('toast-container').appendChild(t);
  setTimeout(() => removeToast(t), duration);
}
function removeToast(t) { t.classList.add('removing'); setTimeout(() => t.remove(), 320); }
window.showToast = showToast;

/* ── Sidebar (mobile) ─────────────────── */
function openNav()  { document.getElementById('mySidenav').style.width='250px'; document.getElementById('overlay').style.display='block'; }
function closeNav() { document.getElementById('mySidenav').style.width='0';     document.getElementById('overlay').style.display='none'; }
document.addEventListener('DOMContentLoaded', function () {
  const ov = document.getElementById('overlay'); if (ov) ov.addEventListener('click', closeNav);
});
window.openNav = openNav; window.closeNav = closeNav;

/* ── Animated counter ─────────────────── */
function animateCount(el, target, duration = 1100) {
  let startTime = null;
  function step(ts) {
    if (!startTime) startTime = ts;
    const p = Math.min((ts - startTime) / duration, 1);
    const ease = 1 - Math.pow(1 - p, 3);
    el.textContent = Math.floor(ease * target) + (el.dataset.suffix || '');
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}
window.animateCount = animateCount;

/* ── Card reveal ──────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.style.animation = 'fadeIn .4s ease both'; obs.unobserve(e.target); } });
  }, { threshold: 0.08 });
  document.querySelectorAll('.sg-card,.sg-card-flat').forEach(c => obs.observe(c));
});
</script>
