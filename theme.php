<!-- StudyGenie Shared Design System -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════
   CSS VARIABLES & RESET
═══════════════════════════════════════════ */
:root {
  --g1: #1b5e20;
  --g2: #2e7d32;
  --g3: #388e3c;
  --g4: #43a047;
  --g5: #66bb6a;
  --accent: #00e676;
  --glass-bg: rgba(255,255,255,0.82);
  --glass-border: rgba(255,255,255,0.6);
  --glass-shadow: 0 8px 32px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
  --text: #1a1a2e;
  --text-muted: #6b7280;
  --radius: 18px;
  --radius-sm: 12px;
  --transition: .25s cubic-bezier(.4,0,.2,1);
}

*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}

html { scroll-behavior: smooth; }

body {
  font-family: 'Inter', 'Segoe UI', sans-serif;
  min-height: 100vh;
  background: #0d2b14;
  color: var(--text);
  overflow-x: hidden;
}

/* ═══════════════════════════════════════════
   ANIMATED BACKGROUND
═══════════════════════════════════════════ */
body::before {
  content: '';
  position: fixed;
  inset: 0;
  z-index: -2;
  background:
    radial-gradient(ellipse 80% 60% at 20% 20%, #1b5e20cc, transparent),
    radial-gradient(ellipse 60% 80% at 80% 80%, #004d40cc, transparent),
    radial-gradient(ellipse 50% 50% at 60% 30%, #1a237eaa, transparent),
    #0a1628;
  animation: bgShift 14s ease-in-out infinite alternate;
}

body::after {
  content: '';
  position: fixed;
  inset: 0;
  z-index: -1;
  background-image:
    radial-gradient(circle at 25% 60%, rgba(76,175,80,.08) 0%, transparent 50%),
    radial-gradient(circle at 75% 20%, rgba(0,230,118,.06) 0%, transparent 45%);
  animation: bgShift2 18s ease-in-out infinite alternate;
}

@keyframes bgShift {
  0%   { background-position: 0% 0%; }
  50%  { background-position: 100% 50%; filter: hue-rotate(15deg); }
  100% { background-position: 50% 100%; }
}
@keyframes bgShift2 {
  0%   { opacity: .6; transform: scale(1); }
  100% { opacity: 1;  transform: scale(1.05); }
}

/* ═══════════════════════════════════════════
   PARTICLES (floating dots)
═══════════════════════════════════════════ */
.particles {
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
}
.particle {
  position: absolute;
  border-radius: 50%;
  background: rgba(76,175,80,.18);
  animation: float linear infinite;
}
@keyframes float {
  0%   { transform: translateY(100vh) scale(0); opacity: 0; }
  10%  { opacity: 1; }
  90%  { opacity: .4; }
  100% { transform: translateY(-10vh) scale(1); opacity: 0; }
}

/* ═══════════════════════════════════════════
   PAGE FADE-IN
═══════════════════════════════════════════ */
.page-wrapper {
  animation: fadeIn .45s ease both;
  padding: 22px;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ═══════════════════════════════════════════
   GLASS HEADER
═══════════════════════════════════════════ */
.sg-header {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  padding: 14px 24px;
  border-radius: var(--radius);
  box-shadow: var(--glass-shadow);
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 22px;
  position: sticky;
  top: 12px;
  z-index: 100;
}

.sg-logo {
  font-size: 24px;
  font-weight: 900;
  background: linear-gradient(135deg, #2e7d32, #00e676);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  cursor: pointer;
  letter-spacing: -0.5px;
  user-select: none;
}

.sg-header-right {
  font-size: 13px;
  color: var(--text-muted);
  font-weight: 500;
}

/* ═══════════════════════════════════════════
   GLASS CARD
═══════════════════════════════════════════ */
.sg-card {
  background: var(--glass-bg);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius);
  box-shadow: var(--glass-shadow);
  padding: 28px;
  transition: transform var(--transition), box-shadow var(--transition);
}
.sg-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 16px 48px rgba(0,0,0,0.16);
}
.sg-card-flat {
  background: var(--glass-bg);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius);
  box-shadow: var(--glass-shadow);
  padding: 28px;
}

/* ═══════════════════════════════════════════
   BUTTONS
═══════════════════════════════════════════ */
.sg-btn {
  position: relative;
  overflow: hidden;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 22px;
  border: none;
  border-radius: var(--radius-sm);
  font-family: inherit;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  transition: transform var(--transition), opacity var(--transition), box-shadow var(--transition);
  white-space: nowrap;
}
.sg-btn:hover  { transform: translateY(-2px); opacity: .92; }
.sg-btn:active { transform: scale(.97); }

.sg-btn-primary {
  background: linear-gradient(135deg, #2e7d32, #43a047);
  color: #fff;
  box-shadow: 0 4px 16px rgba(46,125,50,.35);
}
.sg-btn-primary:hover { box-shadow: 0 8px 24px rgba(46,125,50,.45); }

.sg-btn-ghost {
  background: rgba(76,175,80,.12);
  color: #2e7d32;
  border: 1.5px solid rgba(76,175,80,.3);
}
.sg-btn-ghost:hover { background: rgba(76,175,80,.2); }

.sg-btn-danger {
  background: rgba(229,57,53,.1);
  color: #c62828;
  border: 1.5px solid rgba(229,57,53,.25);
}
.sg-btn-danger:hover { background: rgba(229,57,53,.18); }

/* Ripple */
.ripple {
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,.35);
  transform: scale(0);
  animation: rippleAnim .55s linear;
  pointer-events: none;
}
@keyframes rippleAnim {
  to { transform: scale(4); opacity: 0; }
}

/* ═══════════════════════════════════════════
   INPUTS
═══════════════════════════════════════════ */
.sg-input {
  width: 100%;
  padding: 13px 16px;
  border: 1.5px solid rgba(0,0,0,.12);
  border-radius: var(--radius-sm);
  font-family: inherit;
  font-size: 15px;
  background: rgba(255,255,255,.9);
  color: var(--text);
  transition: border-color var(--transition), box-shadow var(--transition);
  outline: none;
}
.sg-input:focus {
  border-color: #43a047;
  box-shadow: 0 0 0 3px rgba(67,160,71,.18);
}

/* ═══════════════════════════════════════════
   TOAST NOTIFICATIONS
═══════════════════════════════════════════ */
#toast-container {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
}
.toast {
  pointer-events: all;
  padding: 14px 18px;
  border-radius: 14px;
  font-size: 14px;
  font-weight: 600;
  backdrop-filter: blur(16px);
  border: 1px solid;
  box-shadow: 0 8px 24px rgba(0,0,0,.18);
  animation: toastIn .3s ease both;
  display: flex;
  align-items: center;
  gap: 10px;
  max-width: 340px;
  cursor: pointer;
}
.toast.removing { animation: toastOut .3s ease forwards; }
.toast-success { background: rgba(200,230,201,.92); color: #1b5e20; border-color: rgba(76,175,80,.4); }
.toast-error   { background: rgba(255,205,210,.92); color: #b71c1c; border-color: rgba(229,57,53,.4); }
.toast-info    { background: rgba(187,222,251,.92); color: #0d47a1; border-color: rgba(33,150,243,.4); }
@keyframes toastIn  { from { opacity:0; transform: translateX(60px) scale(.92); } to { opacity:1; transform: none; } }
@keyframes toastOut { to   { opacity:0; transform: translateX(60px) scale(.92); } }

/* ═══════════════════════════════════════════
   SKELETON LOADER
═══════════════════════════════════════════ */
.skeleton {
  background: linear-gradient(90deg, rgba(0,0,0,.06) 25%, rgba(0,0,0,.12) 50%, rgba(0,0,0,.06) 75%);
  background-size: 400% 100%;
  animation: shimmer 1.4s ease infinite;
  border-radius: 8px;
}
@keyframes shimmer { to { background-position: -400% 0; } }

/* ═══════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════ */
.sidenav {
  height: 100%;
  width: 0;
  position: fixed;
  z-index: 200;
  top: 0;
  left: 0;
  background: rgba(10,22,40,.96);
  backdrop-filter: blur(24px);
  overflow-x: hidden;
  transition: width .4s cubic-bezier(.4,0,.2,1);
  padding-top: 70px;
  border-right: 1px solid rgba(76,175,80,.15);
}
.sidenav a {
  padding: 13px 14px 13px 28px;
  text-decoration: none;
  font-size: 16px;
  font-weight: 600;
  color: rgba(255,255,255,.65);
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all .2s;
  border-left: 3px solid transparent;
  letter-spacing: .2px;
}
.sidenav a:hover {
  color: #fff;
  background: rgba(76,175,80,.1);
  border-left-color: #4caf50;
}
.sidenav .closebtn {
  position: absolute;
  top: 14px;
  right: 18px;
  font-size: 28px;
  color: rgba(255,255,255,.5);
  cursor: pointer;
  background: none;
  border: none;
  font-family: inherit;
  line-height: 1;
  transition: color .2s;
}
.sidenav .closebtn:hover { color: #fff; }
.sidenav .nav-brand {
  position: absolute;
  top: 18px;
  left: 22px;
  font-size: 20px;
  font-weight: 900;
  background: linear-gradient(135deg,#4caf50,#00e676);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
#overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.55);
  backdrop-filter: blur(3px);
  display: none;
  z-index: 150;
  transition: opacity .3s;
}

/* ═══════════════════════════════════════════
   MISC UTILITIES
═══════════════════════════════════════════ */
.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
}
.badge-green   { background: rgba(76,175,80,.15);  color: #2e7d32; }
.badge-yellow  { background: rgba(255,193,7,.15);  color: #856404; }
.badge-red     { background: rgba(244,67,54,.12);  color: #c62828; }
.badge-blue    { background: rgba(33,150,243,.12); color: #0d47a1; }

.section-title {
  font-size: 18px;
  font-weight: 800;
  color: var(--g2);
  margin-bottom: 16px;
  letter-spacing: -.3px;
}

.text-muted { color: var(--text-muted); }
.text-green { color: var(--g2); }

a { color: inherit; text-decoration: none; }
</style>

<!-- Particles container -->
<div class="particles" id="particles"></div>
<!-- Toast container -->
<div id="toast-container"></div>

<script>
/* ── Particles ─────────────────────────── */
(function(){
  const c = document.getElementById('particles');
  if (!c) return;
  for (let i = 0; i < 18; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    const s = Math.random() * 12 + 4;
    p.style.cssText = `
      width:${s}px;height:${s}px;
      left:${Math.random()*100}%;
      animation-duration:${Math.random()*18+12}s;
      animation-delay:${Math.random()*14}s;
      opacity:${Math.random()*.4+.1};
    `;
    c.appendChild(p);
  }
})();

/* ── Ripple on all .sg-btn ─────────────── */
document.addEventListener('click', function(e) {
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

/* ── Toast system ──────────────────────── */
function showToast(msg, type = 'info', duration = 3500) {
  const icons = { success: '✅', error: '❌', info: 'ℹ️' };
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.innerHTML = `<span>${icons[type]||'ℹ️'}</span><span>${msg}</span>`;
  t.onclick = () => removeToast(t);
  document.getElementById('toast-container').appendChild(t);
  setTimeout(() => removeToast(t), duration);
}
function removeToast(t) {
  t.classList.add('removing');
  setTimeout(() => t.remove(), 320);
}
window.showToast = showToast;

/* ── Sidebar ───────────────────────────── */
function openNav() {
  document.getElementById('mySidenav').style.width = '260px';
  document.getElementById('overlay').style.display = 'block';
}
function closeNav() {
  document.getElementById('mySidenav').style.width = '0';
  document.getElementById('overlay').style.display = 'none';
}
document.addEventListener('DOMContentLoaded', function() {
  const ov = document.getElementById('overlay');
  if (ov) ov.addEventListener('click', closeNav);
});
window.openNav  = openNav;
window.closeNav = closeNav;

/* ── Animated counter ──────────────────── */
function animateCount(el, target, duration = 1200) {
  let start = 0, startTime = null;
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

/* ── Intersection observer for card reveal */
document.addEventListener('DOMContentLoaded', function() {
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.animation = 'fadeIn .45s ease both';
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.sg-card,.sg-card-flat').forEach(c => obs.observe(c));
});
</script>
