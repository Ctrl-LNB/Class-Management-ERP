<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data.php';

$state = loadData();
$initialState = publicData($state);
$initialIsAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title id="docTitle"><?php echo htmlspecialchars($initialState['meta']['className'] ?? 'CSE60F'); ?> — Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" href="uploads/assets/uu-logo.png">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#F3E8BC;
  --surface:#FFFFFF;
  --ink:#121212;
  --ink-soft:#333333;
  --green:#035352;
  --green-dark:#035352;
  --marigold:#F3E8BC;
  --chalk:#F3E8BC;
  --danger:#C1503F;
  --line:#D3C79A;
  --card-bg:#FFFFFF;
  --input-bg:#FAF5E4;
  --table-th-bg:#EFE4B5;
  --radius:14px;
}

[data-theme="dark"] {
  --bg:#0A1212;
  --surface:#121C1C;
  --ink:#F3E8BC;
  --ink-soft:#D5CA9F;
  --green:#035352;
  --green-dark:#052424;
  --marigold:#F3E8BC;
  --chalk:#F3E8BC;
  --danger:#E53E3E;
  --line:#1E3030;
  --card-bg:#121C1C;
  --input-bg:#0A1212;
  --table-th-bg:#1A2828;
}

*{box-sizing:border-box;}
body{margin:0;background:var(--bg);color:var(--ink);font-family:'Inter',sans-serif;transition:background 0.2s ease, color 0.2s ease;}
h1,h2,h3{font-family:'Fraunces',serif;margin:0;}
.mono{font-family:'IBM Plex Mono',monospace;}
a{color:inherit;}

.app{
  display:flex;
  min-height:100vh;
  width:100%;
  align-items:flex-start;
}

.sidebar{
  width:220px;
  background:var(--green-dark);
  color:var(--chalk);
  flex-shrink:0;
  display:flex;
  flex-direction:column;
  padding:24px 0;
  transition:all 0.2s ease;
  position:sticky;
  top:0;
  height:100vh;
  overflow-y:auto;
}

.brand{
  padding:0 20px 24px 20px;
  border-bottom:1px solid rgba(243,232,188,0.2);
  margin-bottom:12px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.brand-info{display:flex;align-items:center;gap:12px;}
.brand-img{width:42px;height:42px;border-radius:8px;object-fit:cover;border:1px solid rgba(243,232,188,0.4);display:none;}
.brand h1{font-size:20px;color:var(--chalk);font-weight:600;word-break:break-word;}
.brand p{margin:4px 0 0;font-size:12px;color:rgba(243,232,188,0.7);font-family:'IBM Plex Mono',monospace;}

.brand-actions{display:flex;align-items:center;gap:10px;}
.menu-toggle{display:none;background:transparent;border:none;color:var(--chalk);font-size:22px;cursor:pointer;padding:0;}

.nav-menu{display:flex;flex-direction:column;flex:1;}
.tab{position:relative;padding:14px 20px;margin:2px 0;cursor:pointer;font-size:14px;font-weight:500;color:rgba(243,232,188,0.8);border-left:3px solid transparent;transition:all .15s ease;display:flex;align-items:center;justify-content:space-between;}
.tab:hover{background:rgba(243,232,188,0.12);color:var(--chalk);}
.tab.active{background:var(--bg);color:#121212;border-radius:8px 0 0 8px;margin-right:-1px;font-weight:700;border-left:4px solid #121212;}
[data-theme="dark"] .tab.active{color:var(--chalk);border-left-color:var(--chalk);}

.theme-icon-btn{
  background:transparent;
  border:none;
  color:var(--chalk);
  font-size:18px;
  cursor:pointer;
  padding:4px 8px;
  border-radius:6px;
  transition:background 0.2s ease, transform 0.15s ease;
  display:inline-flex;
  align-items:center;
  justify-content:center;
}
.theme-icon-btn:hover{background:rgba(243,232,188,0.2);transform:scale(1.1);}
.admin-badge{padding:8px 20px;margin-top:auto;font-size:11px;font-family:'IBM Plex Mono',monospace;color:var(--chalk);display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(243,232,188,0.2);}

.main{flex:1;padding:28px 36px;width:calc(100% - 220px);}
.topbar{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px;width:100%;}
.topbar h2{font-size:26px;}
.clock{font-family:'IBM Plex Mono',monospace;font-size:13px;color:var(--ink-soft);text-align:right;}

.countdown-card{background:var(--green-dark);color:var(--chalk);border-radius:var(--radius);padding:20px 24px;margin-bottom:22px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;position:relative;overflow:hidden;border:1px solid #121212;width:100%;}
.countdown-card::before{content:"";position:absolute;top:0;left:0;right:0;bottom:0;background-image:repeating-linear-gradient(0deg, rgba(243,232,188,0.04) 0px, rgba(243,232,188,0.04) 1px, transparent 1px, transparent 28px);pointer-events:none;}
.countdown-label{font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:rgba(243,232,188,0.7);margin-bottom:6px;}
.countdown-sub{font-size:18px;font-weight:600;font-family:'Fraunces',serif;}
.countdown-meta{font-size:12px;color:rgba(243,232,188,0.75);margin-top:4px;}
.countdown-timer{font-family:'IBM Plex Mono',monospace;font-size:34px;font-weight:600;letter-spacing:1px;color:var(--chalk);z-index:1;}

.card{background:var(--card-bg);border-radius:var(--radius);padding:20px 22px;margin-bottom:18px;border:1.5px solid #121212;width:100%;}
[data-theme="dark"] .card{border-color:var(--line);}

.card h3{font-size:15px;text-transform:uppercase;letter-spacing:0.6px;color:var(--green);margin-bottom:14px;}
[data-theme="dark"] .card h3{color:var(--chalk);}

.form-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;}
.form-row input, .form-row select, .form-row textarea{
  flex:1;min-width:120px;padding:8px 10px;border:1px solid #121212;border-radius:6px;font-family:'Inter',sans-serif;font-size:13px;background:var(--input-bg);color:var(--ink);
}
[data-theme="dark"] .form-row input, [data-theme="dark"] .form-row select, [data-theme="dark"] .form-row textarea{border-color:var(--line);}

textarea{resize:vertical;min-height:60px;}
button{background:#121212;color:var(--chalk);border:none;padding:9px 16px;border-radius:6px;font-size:13px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:all 0.15s ease;}
button:hover{background:var(--green);color:#fff;}
button.secondary{background:transparent;color:#121212;border:1.5px solid #121212;}
button.secondary:hover{background:#121212;color:var(--chalk);}
[data-theme="dark"] button.secondary{color:var(--chalk);border-color:var(--chalk);}
[data-theme="dark"] button.secondary:hover{background:var(--chalk);color:#121212;}

button.danger{background:transparent;color:var(--danger);border:1.5px solid var(--danger);}
button.danger:hover{background:var(--danger);color:#fff;}
button.small{padding:4px 8px;font-size:11px;}

.table-responsive{overflow-x:auto;width:100%;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:left;padding:10px;border-bottom:2px solid #121212;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--ink);background:var(--table-th-bg);}
[data-theme="dark"] th{border-bottom-color:var(--line);}

td{padding:10px;border-bottom:1px solid var(--line);border-right:1px solid var(--line);vertical-align:top;}
td:last-child{border-right:none;}
tr.overdue td{color:var(--danger);}
tr.soon td{background:rgba(3,83,82,0.12);}
tr.done td{color:#777;text-decoration:line-through;}

.routine-grid-cell{display:flex;flex-direction:column;gap:4px;font-size:12px;background:var(--input-bg);padding:8px;border-radius:6px;border:1px solid var(--line);margin-bottom:4px;}
.routine-grid-cell .subj{font-weight:700;color:var(--ink);}
.routine-grid-cell .meta{color:var(--ink-soft);font-size:11px;}

/* Student Card Grid */
.student-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));gap:12px;margin:14px 0;}
.student-card{
  border:1.5px solid #121212;
  background:var(--input-bg);
  border-radius:8px;
  padding:12px;
  cursor:pointer;
  user-select:none;
  transition:all 0.15s ease;
  position:relative;
  display:flex;
  flex-direction:column;
}
[data-theme="dark"] .student-card{border-color:var(--line);}
.student-card:hover{border-color:var(--green);}
.student-card.present{background:rgba(3, 83, 82, 0.2);border-color:var(--green);}
.student-card .sid{font-family:'IBM Plex Mono',monospace;font-size:16px;color:var(--green);font-weight:700;letter-spacing:0.5px;}
[data-theme="dark"] .student-card .sid{color:var(--chalk);}
.student-card .sname{font-weight:500;font-size:12px;margin-top:2px;color:var(--ink-soft);word-break:break-word;}
.student-card .status-pill{display:inline-block;margin-top:8px;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;text-transform:uppercase;background:var(--line);color:var(--ink);align-self:flex-start;}
.student-card.present .status-pill{background:var(--green);color:var(--chalk);}
.student-card .del-btn{position:absolute;top:6px;right:6px;background:transparent;color:var(--danger);border:none;font-size:14px;padding:2px 6px;cursor:pointer;}

/* Faculty Card Grid */
.faculty-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;}
.advisor-grid{display:flex;flex-direction:column;align-items:center;gap:20px;}

.faculty-card{
  border:1.5px solid #121212;
  border-radius:12px;
  padding:20px 18px;
  position:relative;
  background:var(--surface);
  display:flex;
  flex-direction:column;
  align-items:center;
  text-align:center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  width:100%;
}
.faculty-card:hover{transform: translateY(-2px);box-shadow: 0 6px 16px rgba(0,0,0,0.08);border-color: var(--green);}
[data-theme="dark"] .faculty-card{border-color:var(--line);box-shadow:none;}
[data-theme="dark"] .faculty-card:hover{border-color:var(--chalk);}

.faculty-card .avatar-wrap{width:82px;height:82px;border-radius:50%;overflow:hidden;margin-bottom:12px;border:2px solid var(--green);background:var(--input-bg);flex-shrink:0;box-shadow: 0 3px 6px rgba(0,0,0,0.1);}
[data-theme="dark"] .faculty-card .avatar-wrap{border-color:var(--chalk);}
.faculty-card .avatar-wrap img{width:100%;height:100%;object-fit:cover;}

.faculty-card h4{font-family:'Fraunces',serif;font-size:18px;font-weight:700;margin-bottom:2px;color:var(--ink);line-height:1.2;}
.faculty-card .role{font-size:12px;font-weight:600;color:var(--green);margin-bottom:12px;letter-spacing:0.2px;}
[data-theme="dark"] .faculty-card .role{ color:var(--chalk); }

.faculty-card .details-box{width:100%;background:var(--input-bg);border:1px solid var(--line);border-radius:8px;padding:10px 12px;margin-bottom:12px;display:flex;flex-direction:column;gap:6px;font-size:12px;text-align:left;}
.faculty-card .detail-item{display:flex;align-items:center;gap:8px;color:var(--ink-soft);word-break:break-word;}
.faculty-card .detail-item strong{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--ink);}

.faculty-card .code-pill{width:100%;background:var(--surface);border:1.5px dashed var(--green);padding:8px 12px;border-radius:8px;font-family:'IBM Plex Mono',monospace;font-size:12px;display:flex;justify-content:space-between;align-items:center;margin-top:auto;}
[data-theme="dark"] .faculty-card .code-pill{ border-color:var(--line); }
.faculty-card .code-pill button{padding:4px 10px;font-size:10px;border-radius:4px;}

.faculty-card .top-controls{position:absolute;top:10px;right:10px;display:flex;gap:4px;}
.faculty-card .top-controls button{background:transparent;border:none;font-size:14px;cursor:pointer;padding:4px;border-radius:4px;line-height:1;}

.att-preview{white-space:pre-wrap;background:var(--input-bg);border:1px dashed var(--green);border-radius:8px;padding:14px;font-family:'IBM Plex Mono',monospace;font-size:12.5px;line-height:1.6;}
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;}
.badge.pending{background:rgba(3,83,82,0.2);color:var(--green);}
.badge.done{background:var(--line);color:var(--ink-soft);}

/* Notice Card Layout */
.notice-card-box {
  border: 1.5px solid #121212;
  border-radius: 8px;
  padding: 16px;
  background: var(--input-bg);
  margin-bottom: 12px;
}
[data-theme="dark"] .notice-card-box { border-color: var(--line); }

/* Edit Modal */
.modal-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:999;padding:16px;}
.modal-content{background:var(--surface);border-radius:var(--radius);padding:24px;width:100%;max-width:500px;box-shadow:0 10px 25px rgba(0,0,0,0.2);border:1px solid var(--line);}

.empty{color:var(--ink-soft);font-size:13px;font-style:italic;padding:8px 0;}
.hint{font-size:11px;color:var(--ink-soft);margin-top:-4px;margin-bottom:12px;}

@media(max-width:800px){
  .app{flex-direction:column;}
  .sidebar{
    width:100%;
    padding:12px 16px;
    position:sticky;
    top:0;
    z-index:100;
    height:auto;
    max-height:100vh;
    box-shadow:0 2px 10px rgba(0,0,0,0.15);
  }
  .brand{padding:0 0 4px 0;border-bottom:none;margin-bottom:0;}
  .menu-toggle{display:block;}
  .nav-menu{
    display:none;
    margin-top:12px;
    border-top:1px solid rgba(243,232,188,0.2);
    padding-top:8px;
    max-height:calc(100vh - 70px);
    overflow-y:auto;
  }
  .nav-menu.open{display:flex;}
  .tab{border-left:none;border-radius:6px;margin-right:0;}
  .tab.active{border-radius:6px;border-left:none;}
  .admin-badge{margin-top:8px;border-top:1px solid rgba(243,232,188,0.2);padding:12px 0 4px 0;}
  .main{padding:16px;width:100%;}
}
</style>
</head>
<body>
<div class="app">
  <div class="sidebar">
    <div class="brand">
      <div class="brand-info">
        <img id="brandHeaderImg" class="brand-img" src="" alt="Brand Logo">
        <div>
          <h1 id="brandClassName">CSE60F</h1>
          <p id="brandSemester">Summer 2026</p>
        </div>
      </div>
      <div class="brand-actions">
        <button class="theme-icon-btn" onclick="toggleTheme()" title="Toggle Theme" id="navThemeIconHeader">🌙</button>
        <button class="menu-toggle" id="menuToggleBtn" onclick="toggleMenu()">☰</button>
      </div>
    </div>
    <div class="nav-menu" id="navMenu">
      <div class="tab" data-tab="dashboard">Dashboard</div>
      <div class="tab" data-tab="notice">Notice Board</div>
      <div class="tab" data-tab="routine">Class Routine</div>
      <div class="tab" data-tab="exam">Exam Routine</div>
      <div class="tab" data-tab="faculty">Faculty</div>
      <div class="tab" data-tab="attendance">Attendance</div>
      <div class="tab" data-tab="tasks">Tasks</div>
      <div class="tab" data-tab="settings">Settings</div>

      <div class="admin-badge" id="adminStatus">
        <span>Mode: Reader</span>
      </div>
    </div>
  </div>
  <div class="main">
    <div class="topbar">
      <h2 id="pageTitle">Dashboard</h2>
      <div class="clock mono" id="liveClock"></div>
    </div>
    <div id="content"></div>
  </div>
</div>

<!-- Modal Container -->
<div id="modalContainer"></div>

<script>
let state = <?php echo json_encode($initialState, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
let isAdmin = <?php echo json_encode($initialIsAdmin); ?>;

const THEME_KEY = 'crThemeMode';
const DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
const DEFAULT_AVATAR = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23035352'><path d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/></svg>";

/* State for keeping track of form inputs during attendance toggling */
let attendanceDateInput = new Date().toISOString().slice(0, 10);
let attendanceSubjectInput = '';

function val(id){
  const el = document.getElementById(id);
  return el ? el.value.trim() : '';
}
function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
function escapeJs(str) {
  if (!str) return '';
  return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
}

/* ---------- API HELPER WITH MULTIPART SUPPORT ---------- */
async function api(action, payload = {}, fileInput = null, fileFieldName = 'imageFile'){
  try{
    let body;
    let headers = {};

    if (fileInput && fileInput.files.length > 0) {
      body = new FormData();
      body.append('action', action);
      Object.keys(payload).forEach(key => body.append(key, payload[key]));
      body.append(fileFieldName, fileInput.files[0]);
    } else {
      headers['Content-Type'] = 'application/json';
      body = JSON.stringify({ action, ...payload });
    }

    const res = await fetch('api.php', { method: 'POST', headers, body });
    const json = await res.json();
    if(!json.ok){
      alert(json.error || 'Something went wrong.');
      return null;
    }
    if(json.state) state = json.state;
    return json;
  }catch(err){
    alert('Network error: could not reach the server.');
    return null;
  }
}

/* ---------- THEME SYSTEM ---------- */
function initTheme(){
  const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
  document.documentElement.setAttribute('data-theme', savedTheme);
  updateThemeUI(savedTheme);
}
function toggleTheme(){
  const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem(THEME_KEY, next);
  updateThemeUI(next);
}
function updateThemeUI(theme){
  const navHeaderIcon = document.getElementById('navThemeIconHeader');
  const themeSymbol = theme === 'dark' ? '☀️' : '🌙';
  if(navHeaderIcon) navHeaderIcon.textContent = themeSymbol;
}
initTheme();

function formatTime12(time24) {
  if (!time24) return '';
  const [h, m] = time24.split(':').map(Number);
  const period = h >= 12 ? 'PM' : 'AM';
  const h12 = h % 12 || 12;
  return `${String(h12).padStart(2, '0')}:${String(m).padStart(2, '0')} ${period}`;
}

let currentTab = 'dashboard';
let presentSet = new Set();

/* ---------- NAVIGATION ---------- */
function navigateToTab(tabName){
  currentTab = tabName;
  closeMenu();
  render();
}
function closeMenu(){
  const navMenu = document.getElementById('navMenu');
  const menuBtn = document.getElementById('menuToggleBtn');
  if(navMenu) navMenu.classList.remove('open');
  if(menuBtn) menuBtn.textContent = '☰';
}
function toggleMenu(){
  const navMenu = document.getElementById('navMenu');
  const menuBtn = document.getElementById('menuToggleBtn');
  if(navMenu){
    navMenu.classList.toggle('open');
    if(menuBtn){
      menuBtn.textContent = navMenu.classList.contains('open') ? '✕' : '☰';
    }
  }
}
document.querySelectorAll('.tab').forEach(t=>{
  t.addEventListener('click', ()=>{
    currentTab = t.dataset.tab;
    closeMenu();
    render();
  });
});

function renderSidebarCounts(){
  document.querySelectorAll('.tab').forEach(t=>t.classList.toggle('active', t.dataset.tab===currentTab));
  const badge = document.getElementById('adminStatus');
  if(isAdmin) {
    badge.innerHTML = `<span>Mode: Admin</span><button class="small secondary" style="color:var(--chalk);border-color:var(--chalk);" onclick="logout()">Logout</button>`;
  } else {
    badge.innerHTML = `<span>Mode: Reader</span>`;
  }
}

function updateHeaderInfo(){
  const className = (state.meta && state.meta.className) ? state.meta.className : 'CSE60F';
  document.getElementById('brandClassName').textContent = className;
  document.getElementById('docTitle').textContent = `${className} — Dashboard`;
  document.getElementById('brandSemester').textContent = (state.meta && state.meta.semester) ? state.meta.semester : 'Summer 2026';

  const brandImg = document.getElementById('brandHeaderImg');
  if(brandImg){
    if(state.meta && state.meta.brandImageUrl){
      brandImg.src = state.meta.brandImageUrl;
      brandImg.style.display = 'block';
    } else {
      brandImg.style.display = 'none';
      brandImg.src = '';
    }
  }
}

/* ---------- COUNTDOWN & CLOCK ---------- */
function computeNextClass(){
  const now = new Date();
  for(let offset=0; offset<8; offset++){
    const d = new Date(now); d.setDate(now.getDate()+offset);
    const dayName = DAYS[d.getDay()];
    const periods = (state.classRoutine[dayName]||[]).slice().sort((a,b)=>a.start.localeCompare(b.start));
    for(const p of periods){
      const [sh,sm] = p.start.split(':').map(Number);
      const [eh,em] = p.end.split(':').map(Number);
      const startTarget = new Date(d.getFullYear(), d.getMonth(), d.getDate(), sh, sm, 0);
      const endTarget = new Date(d.getFullYear(), d.getMonth(), d.getDate(), eh, em, 0);
      if(offset===0 && now >= startTarget && now < endTarget){
        return { period:p, day:dayName, target:endTarget, isToday:true, isOngoing:true };
      }
      if(startTarget > now){
        return { period:p, day:dayName, target:startTarget, isToday: offset===0, isOngoing:false };
      }
    }
  }
  return null;
}

function tickCountdown(){
  const nc = computeNextClass();
  const timerEl = document.getElementById('countdownTimer');
  const subjEl = document.getElementById('nextClassSubject');
  const metaEl = document.getElementById('nextClassMeta');
  const labelEl = document.getElementById('countdownLabel');
  if(!timerEl || !subjEl || !metaEl) return;

  if(!nc){
    if(labelEl) labelEl.textContent = 'Next Class';
    timerEl.textContent = '--:--:--';
    subjEl.textContent = 'No upcoming class';
    metaEl.textContent = 'Add periods in Class Routine to see a countdown';
    return;
  }
  const diff = nc.target - new Date();
  const h = Math.floor(diff/3600000);
  const m = Math.floor((diff%3600000)/60000);
  const s = Math.floor((diff%60000)/1000);
  timerEl.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  subjEl.textContent = nc.period.subject || '(untitled subject)';
  const when = nc.isToday ? 'Today' : nc.day;
  if(labelEl) labelEl.textContent = nc.isOngoing ? 'Ongoing · Ends In' : 'Next Class';
  const timeRange = `${formatTime12(nc.period.start)}–${formatTime12(nc.period.end)}${nc.period.faculty ? ' · '+nc.period.faculty : ''}${nc.period.room ? ' · Room '+nc.period.room : ''}`;
  metaEl.textContent = nc.isOngoing ? `Now, ${timeRange}` : `${when}, ${timeRange}`;
}
setInterval(tickCountdown, 1000);

function tickClock(){
  const clockEl = document.getElementById('liveClock');
  if (clockEl) {
    clockEl.textContent = new Date().toLocaleString('en-GB', {
      weekday: 'short', day: '2-digit', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
    });
  }
}
setInterval(tickClock, 1000);

/* ---------- RENDER ROUTER ---------- */
const TITLES = { dashboard:'Dashboard', notice:'Notice Board', routine:'Class Routine', exam:'Exam Routine', faculty:'Faculty', attendance:'Attendance', tasks:'Tasks', settings:'Settings' };

function render(){
  renderSidebarCounts();
  document.getElementById('pageTitle').textContent = TITLES[currentTab];
  updateHeaderInfo();
  updateThemeUI(document.documentElement.getAttribute('data-theme'));
  const c = document.getElementById('content');
  if(currentTab==='dashboard') c.innerHTML = viewDashboard();
  else if(currentTab==='notice') c.innerHTML = viewNotice();
  else if(currentTab==='routine') c.innerHTML = viewRoutine();
  else if(currentTab==='exam') c.innerHTML = viewExam();
  else if(currentTab==='faculty') c.innerHTML = viewFaculty();
  else if(currentTab==='attendance') c.innerHTML = viewAttendance();
  else if(currentTab==='tasks') c.innerHTML = viewTasks();
  else if(currentTab==='settings') c.innerHTML = viewSettings();
  attachHandlers();
  tickCountdown(); tickClock();
}

function attachHandlers() {
  if (currentTab === 'attendance') {
    livePreview();
  }
}

/* ---------- DASHBOARD ---------- */
function viewDashboard(){
  const todayStr = new Date().toISOString().slice(0, 10);
  
  // Filter ONLY pinned tasks to be rendered on the Dashboard card
  const pinnedDashboardTasks = (state.tasks || []).filter(t => 
    t.isPinned !== false && (isAdmin || (t.status === 'pending' && t.deadline >= todayStr))
  );

  const pendingCount = (state.tasks || []).filter(t => t.status === 'pending').length;
  const visibleExams = (state.examRoutine || []).filter(e => isAdmin || (e.date >= todayStr && e.isPinned !== false));
  const upcomingCount = (state.examRoutine || []).filter(e => e.date >= todayStr).length;
  const pinnedNotices = (state.notices || []).filter(n => n.isPinned !== false).slice().reverse();
  const noticeCount = (state.notices || []).length;
  const totalStudents = (state.roster || []).length;

  const advisors = [];
  const regularFaculty = [];
  (state.faculty || []).forEach(f => {
    const des = (f.designation || '').toLowerCase();
    if (f.isAdvisor || des.includes('advisor') || des.includes('adviser')) advisors.push(f);
    else regularFaculty.push(f);
  });

  const uniqueFacultyKeys = new Set();
  let uniqueFacultyCount = 0;
  regularFaculty.forEach(f => {
    const email = (f.email || '').trim().toLowerCase();
    if (email !== '') {
      if (!uniqueFacultyKeys.has(email)) {
        uniqueFacultyKeys.add(email);
        uniqueFacultyCount++;
      }
    } else {
      uniqueFacultyCount++;
    }
  });

  let advisorHtml = '';
  if (advisors.length > 0) {
    advisorHtml = `
    <div class="card" style="background: rgba(3,83,82,0.03); max-width: 480px; margin: 0 auto 18px auto;">
      <h3 style="color: var(--green); text-align: center; margin-bottom: 16px;">Section Advisor</h3>
      <div class="advisor-grid">${advisors.map(f => renderFacultyCard(f)).join('')}</div>
    </div>`;
  }

  return `
  <div class="card">
    <h3>At a Glance</h3>
    <div class="form-row">
      <div style="flex:1;min-width:120px;"><div style="font-size:26px;font-family:'Fraunces',serif;">${pendingCount}</div><div class="hint">Pending tasks</div></div>
      <div style="flex:1;min-width:120px;"><div style="font-size:26px;font-family:'Fraunces',serif;">${upcomingCount}</div><div class="hint">Upcoming exams</div></div>
      <div style="flex:1;min-width:120px;cursor:pointer;" onclick="navigateToTab('notice')"><div style="font-size:26px;font-family:'Fraunces',serif;color:var(--green);">${noticeCount}</div><div class="hint" style="color:var(--green);font-weight:600;">Notices ➔</div></div>
      <div style="flex:1;min-width:120px;"><div style="font-size:26px;font-family:'Fraunces',serif;">${uniqueFacultyCount}</div><div class="hint">Faculty on file</div></div>
      <div style="flex:1;min-width:120px;${isAdmin ? 'cursor:pointer;' : ''}" ${isAdmin ? 'onclick="navigateToTab(\'attendance\')"' : ''}>
        <div style="font-size:26px;font-family:'Fraunces',serif;color:var(--green);">${totalStudents}</div>
        <div class="hint" style="color:var(--green);font-weight:600;">Total Students${isAdmin ? ' ➔' : ''}</div>
      </div>
    </div>
  </div>

  ${advisorHtml}

  <div class="countdown-card">
    <div>
      <div class="countdown-label" id="countdownLabel">Next Class</div>
      <div class="countdown-sub" id="nextClassSubject">—</div>
      <div class="countdown-meta" id="nextClassMeta">No upcoming class found in routine</div>
    </div>
    <div class="countdown-timer" id="countdownTimer">--:--:--</div>
  </div>

  ${pinnedNotices.length ? `
    <div class="card">
      <h3>Pinned Notices</h3>
      ${pinnedNotices.map(n => `
        <div class="notice-card-box">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px;">
            <h4 style="font-size:16px; font-family:'Fraunces',serif; margin:0;">${escapeHtml(n.title)}</h4>
            <span class="mono" style="font-size:11px; color:var(--ink-soft);">${n.date || ''}</span>
          </div>
          ${n.note ? `<p style="font-size:13px; color:var(--ink-soft); margin:8px 0 12px 0; white-space:pre-line;">${escapeHtml(n.note)}</p>` : ''}
          <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:8px;">
            ${n.link ? `<a href="${escapeHtml(n.link)}" target="_blank" style="font-size:12px; font-weight:600; text-decoration:none; color:var(--green);">🔗 Open Link</a>` : ''}
            ${n.resource ? `<a href="${escapeHtml(n.resource)}" target="_blank" download style="font-size:12px; font-weight:600; text-decoration:none; color:var(--green);">📎 Download Resource</a>` : ''}
          </div>
        </div>
      `).join('')}
    </div>
  ` : ''}

  <div class="card">
    <h3>Upcoming Exams</h3>
    ${visibleExams.length ? examTable(visibleExams.slice(0,5)) : '<div class="empty">No active exams to display.</div>'}
  </div>

  ${pinnedDashboardTasks.length ? `
    <div class="card">
      <h3>Pinned Tasks</h3>
      ${taskTable(pinnedDashboardTasks.slice(0,5))}
    </div>
  ` : ''}`;
}

/* ---------- NOTICES ---------- */
function viewNotice(){
  let formHtml = '';
  if(isAdmin){
    formHtml = `<div class="card">
      <h3>Add Notice</h3>
      <div class="form-row">
        <input type="text" id="n_title" placeholder="Notice Title">
        <input type="text" id="n_link" placeholder="External Link (Optional e.g. https://...)">
      </div>
      <div class="form-row">
        <textarea id="n_note" placeholder="Notice Details / Instructions"></textarea>
      </div>
      <div class="form-row">
        <div style="flex:1; display:flex; flex-direction:column; gap:2px;">
          <label style="font-size:11px; font-weight:600;">Attach File/Resource (Saved in uploads/notices):</label>
          <input type="file" id="n_file">
        </div>
      </div>
      <button onclick="addNotice()">Post Notice</button>
    </div>`;
  }

  const notices = (state.notices || []).slice().reverse();
  let listHtml = '';

  if(notices.length === 0){
    listHtml = '<div class="empty">No notices published yet.</div>';
  } else {
    listHtml = notices.map(n => {
      const isPinned = n.isPinned !== false;
      return `
      <div class="notice-card-box">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px;">
          <h4 style="font-size:16px; font-family:'Fraunces',serif; margin:0;">${escapeHtml(n.title)}</h4>
          <span class="mono" style="font-size:11px; color:var(--ink-soft);">${n.date || ''}</span>
        </div>
        ${n.note ? `<p style="font-size:13px; color:var(--ink-soft); margin:8px 0 12px 0; white-space:pre-line;">${escapeHtml(n.note)}</p>` : ''}
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:8px;">
          ${n.link ? `<a href="${escapeHtml(n.link)}" target="_blank" style="font-size:12px; font-weight:600; text-decoration:none; color:var(--green);">🔗 Open Link</a>` : ''}
          ${n.resource ? `<a href="${escapeHtml(n.resource)}" target="_blank" download style="font-size:12px; font-weight:600; text-decoration:none; color:var(--green);">📎 Download Resource</a>` : ''}
        </div>
        ${isAdmin ? `<div style="display:flex; gap:6px; margin-top:12px;">
          <button class="small secondary" onclick="openEditNoticeModal('${n.id}')">Edit</button>
          <button class="small ${isPinned ? 'secondary' : ''}" onclick="togglePinNotice('${n.id}')">${isPinned ? '📌 Pinned' : '📍 Unpinned'}</button>
          <button class="small danger" onclick="deleteNotice('${n.id}')">Delete</button>
        </div>` : ''}
      </div>
    `;
    }).join('');
  }

  return `${formHtml}<div class="card"><h3>Notice Dashboard</h3>${listHtml}</div>`;
}

async function addNotice(){
  if(!isAdmin) return;
  const title = val('n_title'), note = val('n_note'), link = val('n_link');
  if(!title){ alert('Please fill in notice title.'); return; }
  const fileInput = document.getElementById('n_file');
  const json = await api('addNotice', { title, note, link }, fileInput, 'noticeFile');
  if(json) render();
}

function openEditNoticeModal(id) {
  const n = (state.notices || []).find(item => item.id === id);
  if (!n) return;

  const modalHtml = `
  <div class="modal-overlay">
    <div class="modal-content">
      <h3>Edit Notice</h3>
      <div class="form-row" style="margin-top:12px;">
        <input type="text" id="m_n_title" value="${escapeHtml(n.title)}" placeholder="Notice Title">
        <input type="text" id="m_n_link" value="${escapeHtml(n.link || '')}" placeholder="External Link">
      </div>
      <div class="form-row">
        <textarea id="m_n_note" placeholder="Notice Details / Instructions">${escapeHtml(n.note || '')}</textarea>
      </div>
      <div class="form-row">
        <div style="flex:1; display:flex; flex-direction:column; gap:2px;">
          <label style="font-size:11px; font-weight:600;">Update File/Resource (Saved in uploads/notices):</label>
          <input type="file" id="m_n_file">
        </div>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">
        <button class="secondary" onclick="closeModal()">Cancel</button>
        <button onclick="submitEditNotice('${n.id}')">Save Changes</button>
      </div>
    </div>
  </div>`;
  document.getElementById('modalContainer').innerHTML = modalHtml;
}

async function submitEditNotice(id) {
  const title = val('m_n_title'), note = val('m_n_note'), link = val('m_n_link');
  if (!title) { alert('Please fill in notice title.'); return; }
  const fileInput = document.getElementById('m_n_file');
  const json = await api('editNotice', { id, title, note, link }, fileInput, 'noticeFile');
  if (json) {
    closeModal();
    render();
  }
}

async function togglePinNotice(id){
  if(!isAdmin) return;
  const json = await api('togglePinNotice', {id});
  if(json) render();
}

async function deleteNotice(id){
  if(!isAdmin) return;
  if(!confirm('Are you sure you want to delete this notice?')) return;
  const json = await api('deleteNotice', { id });
  if(json) render();
}

/* ---------- CLASS ROUTINE ---------- */
function viewRoutine(){
  let html = '';
  if(isAdmin){
    html += `<div class="card"><h3>Add Class Period</h3>
      <div class="form-row">
        <select id="r_day">${DAYS.map(d=>`<option value="${d}">${d}</option>`).join('')}</select>
        <input type="time" id="r_start" placeholder="Start">
        <input type="time" id="r_end" placeholder="End">
      </div>
      <div class="form-row">
        <input type="text" id="r_subject" placeholder="Subject">
        <input type="text" id="r_faculty" placeholder="Faculty">
        <input type="text" id="r_room" placeholder="Room">
      </div>
      <button onclick="addPeriod()">Add Period</button>
    </div>`;
  }

  const activeDays = DAYS.filter(day => (state.classRoutine && state.classRoutine[day] || []).length > 0);
  const timeSlotMap = new Map();
  activeDays.forEach(day => {
    (state.classRoutine[day] || []).forEach(p => {
      const slotKey = `${p.start}-${p.end}`;
      if (!timeSlotMap.has(slotKey)) {
        timeSlotMap.set(slotKey, { start: p.start, end: p.end, label: `${formatTime12(p.start)} - ${formatTime12(p.end)}` });
      }
    });
  });
  const sortedSlots = Array.from(timeSlotMap.values()).sort((a,b) => a.start.localeCompare(b.start));

  html += `<div class="card"><h3>Class Timetable</h3><div class="table-responsive">`;
  if (activeDays.length === 0) {
    html += `<div class="empty">No classes added to routine yet.</div>`;
  } else {
    html += `<table><thead><tr><th style="min-width:100px;">Day</th>
      ${sortedSlots.map(s => `<th class="mono" style="text-align:center; min-width:150px;">${s.label}</th>`).join('')}
      </tr></thead><tbody>`;
    activeDays.forEach(day => {
      html += `<tr><td style="font-weight:600; font-family:'Fraunces',serif; font-size:15px; color:var(--green);">${day}</td>`;
      sortedSlots.forEach(s => {
        const matches = (state.classRoutine[day] || []).filter(p => p.start === s.start && p.end === s.end);
        html += `<td>`;
        matches.forEach(p => {
          html += `<div class="routine-grid-cell">
            <span class="subj">${escapeHtml(p.subject || '(untitled)')}</span>
            <span class="meta">${escapeHtml(p.faculty || '')} ${p.room ? '· Room ' + escapeHtml(p.room) : ''}</span>
            ${isAdmin ? `<div style="display:flex; gap:4px; margin-top:4px;">
              <button class="small secondary" onclick="openEditPeriodModal('${day}', '${p.id}')">Edit</button>
              <button class="small danger" onclick="deletePeriod('${day}','${p.id}')">Remove</button>
            </div>` : ''}
          </div>`;
        });
        html += `</td>`;
      });
      html += `</tr>`;
    });
    html += `</tbody></table>`;
  }
  html += `</div></div>`;
  return html;
}

async function addPeriod(){
  if(!isAdmin) return;
  const day=val('r_day'), start=val('r_start'), end=val('r_end'), subject=val('r_subject'), faculty=val('r_faculty'), room=val('r_room');
  if(!start||!end||!subject){ alert('Please fill start time, end time, and subject.'); return; }
  const json = await api('addPeriod', {day,start,end,subject,faculty,room});
  if(json) render();
}

function openEditPeriodModal(day, id) {
  const p = (state.classRoutine[day] || []).find(item => item.id === id);
  if (!p) return;

  const modalHtml = `
  <div class="modal-overlay">
    <div class="modal-content">
      <h3>Edit Routine Period</h3>
      <div class="form-row" style="margin-top:12px;">
        <input type="time" id="m_r_start" value="${p.start}">
        <input type="time" id="m_r_end" value="${p.end}">
      </div>
      <div class="form-row">
        <input type="text" id="m_r_subject" value="${escapeHtml(p.subject)}" placeholder="Subject">
      </div>
      <div class="form-row">
        <input type="text" id="m_r_faculty" value="${escapeHtml(p.faculty)}" placeholder="Faculty">
        <input type="text" id="m_r_room" value="${escapeHtml(p.room)}" placeholder="Room">
      </div>
      <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">
        <button class="secondary" onclick="closeModal()">Cancel</button>
        <button onclick="submitEditPeriod('${day}', '${p.id}')">Save Changes</button>
      </div>
    </div>
  </div>`;
  document.getElementById('modalContainer').innerHTML = modalHtml;
}

async function submitEditPeriod(day, id) {
  const start = val('m_r_start'), end = val('m_r_end'), subject = val('m_r_subject'), faculty = val('m_r_faculty'), room = val('m_r_room');
  const json = await api('editPeriod', { day, id, start, end, subject, faculty, room });
  if(json) {
    closeModal();
    render();
  }
}

async function deletePeriod(day, id){
  if(!isAdmin) return;
  const json = await api('deletePeriod', {day, id});
  if(json) render();
}

function closeModal() {
  document.getElementById('modalContainer').innerHTML = '';
}

/* ---------- EXAM ROUTINE ---------- */
function examTable(list){
  const today = new Date(new Date().toDateString());
  const rows = list.slice().sort((a,b)=>a.date.localeCompare(b.date)).map(e=>{
    const d = new Date(e.date);
    const diffDays = Math.round((d - today) / 86400000);
    const rowClass = diffDays < 0 ? 'done' : (diffDays <= 2 ? 'soon' : '');
    const isPinned = e.isPinned !== false;
    let daysLabel = '';
    if (diffDays < 0) daysLabel = `<span class="badge" style="background:var(--line); color:var(--ink-soft);">Past</span>`;
    else if (diffDays === 0) daysLabel = `<span class="badge" style="background:var(--danger); color:#fff;">Today</span>`;
    else if (diffDays <= 2) daysLabel = `<span class="badge pending">${diffDays} day(s) left</span>`;
    else daysLabel = `<span class="badge done">${diffDays} day(s) left</span>`;

    const formattedTime = e.time ? formatTime12(e.time) : '';
    return `<tr class="${rowClass}">
      <td style="font-weight:600;">${escapeHtml(e.subject)}</td>
      <td><span class="mono" style="font-size:12px; color:var(--ink-soft);">${escapeHtml(e.examType || 'Exam')}</span></td>
      <td>${escapeHtml(e.topic || '—')}</td>
      <td class="mono" style="white-space:nowrap;">📅 ${e.date}${formattedTime ? ' · ⏰ ' + formattedTime : ''}</td>
      <td>${e.room ? 'Room ' + escapeHtml(e.room) : '—'}</td>
      <td>${daysLabel}</td>
      ${isAdmin ? `<td>
        <button class="small secondary" onclick="openEditExamModal('${e.id}')">Edit</button>
        <button class="small ${isPinned ? 'secondary' : ''}" onclick="togglePinExam('${e.id}')">${isPinned ? '📌 Pinned' : '📍 Unpinned'}</button>
        <button class="small danger" onclick="deleteExam('${e.id}')">Delete</button>
      </td>` : ''}
    </tr>`;
  }).join('');

  return `<div class="table-responsive"><table><thead><tr>
    <th>Subject</th><th>Type</th><th>Exam Topic</th><th>Date & Time</th><th>Room</th><th>Status</th>
    ${isAdmin ? '<th>Admin Controls</th>' : ''}
    </tr></thead><tbody>${rows}</tbody></table></div>`;
}

function viewExam(){
  let formHtml = '';
  if(isAdmin){
    formHtml = `<div class="card"><h3>Add Exam Schedule</h3>
      <div class="form-row">
        <input type="text" id="e_subject" placeholder="Subject Name">
        <select id="e_type">
          <option value="Class Test">Class Test</option>
          <option value="Quiz">Quiz</option>
          <option value="Mid Term">Mid Term</option>
          <option value="Final">Final</option>
          <option value="Assignment / Lab">Assignment / Lab</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="form-row">
        <input type="text" id="e_topic" placeholder="Exam Topic / Syllabus Details">
      </div>
      <div class="form-row">
        <input type="date" id="e_date">
        <input type="time" id="e_time">
        <input type="text" id="e_room" placeholder="Room Number (optional)">
      </div>
      <button onclick="addExam()">Add Exam Schedule</button>
    </div>`;
  }

  const todayStr = new Date().toISOString().slice(0, 10);
  const visibleExams = (state.examRoutine || []).filter(e => isAdmin || (e.date >= todayStr && e.isPinned !== false));

  return `${formHtml}<div class="card"><h3>Exam Routine & Schedules</h3>
    ${visibleExams.length ? examTable(visibleExams) : '<div class="empty">No active exams or tests scheduled.</div>'}
  </div>`;
}

async function addExam(){
  if(!isAdmin) return;
  const subject = val('e_subject'), examType = val('e_type'), topic = val('e_topic'), date = val('e_date'), time = val('e_time'), room = val('e_room');
  if(!subject || !date){ alert('Please fill at least the Subject and Date fields.'); return; }
  const json = await api('addExam', {subject, examType, topic, date, time, room});
  if(json) render();
}

function openEditExamModal(id) {
  const e = (state.examRoutine || []).find(item => item.id === id);
  if (!e) return;

  const modalHtml = `
  <div class="modal-overlay">
    <div class="modal-content">
      <h3>Edit Exam Schedule</h3>
      <div class="form-row" style="margin-top:12px;">
        <input type="text" id="m_e_subject" value="${escapeHtml(e.subject)}" placeholder="Subject Name">
        <select id="m_e_type">
          <option value="Class Test" ${e.examType === 'Class Test' ? 'selected' : ''}>Class Test</option>
          <option value="Quiz" ${e.examType === 'Quiz' ? 'selected' : ''}>Quiz</option>
          <option value="Mid Term" ${e.examType === 'Mid Term' ? 'selected' : ''}>Mid Term</option>
          <option value="Final" ${e.examType === 'Final' ? 'selected' : ''}>Final</option>
          <option value="Assignment / Lab" ${e.examType === 'Assignment / Lab' ? 'selected' : ''}>Assignment / Lab</option>
          <option value="Other" ${e.examType === 'Other' ? 'selected' : ''}>Other</option>
        </select>
      </div>
      <div class="form-row">
        <input type="text" id="m_e_topic" value="${escapeHtml(e.topic || '')}" placeholder="Exam Topic / Syllabus Details">
      </div>
      <div class="form-row">
        <input type="date" id="m_e_date" value="${e.date}">
        <input type="time" id="m_e_time" value="${e.time || ''}">
        <input type="text" id="m_e_room" value="${escapeHtml(e.room || '')}" placeholder="Room Number">
      </div>
      <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">
        <button class="secondary" onclick="closeModal()">Cancel</button>
        <button onclick="submitEditExam('${e.id}')">Save Changes</button>
      </div>
    </div>
  </div>`;
  document.getElementById('modalContainer').innerHTML = modalHtml;
}

async function submitEditExam(id) {
  const subject = val('m_e_subject'), examType = val('m_e_type'), topic = val('m_e_topic'), date = val('m_e_date'), time = val('m_e_time'), room = val('m_e_room');
  if (!subject || !date) { alert('Please fill at least Subject and Date fields.'); return; }
  const json = await api('editExam', { id, subject, examType, topic, date, time, room });
  if (json) {
    closeModal();
    render();
  }
}

async function togglePinExam(id){
  if(!isAdmin) return;
  const json = await api('togglePinExam', {id});
  if(json) render();
}
async function deleteExam(id){
  if(!isAdmin) return;
  if(!confirm('Are you sure you want to delete this exam schedule?')) return;
  const json = await api('deleteExam', {id});
  if(json) render();
}

/* ---------- FACULTY ---------- */
function viewFaculty(){
  const regularFaculty = [];
  (state.faculty || []).forEach(f => {
    const des = (f.designation || '').toLowerCase();
    if (!(f.isAdvisor || des.includes('advisor') || des.includes('adviser'))) regularFaculty.push(f);
  });

  const bySem = {};
  regularFaculty.forEach(f => {
    const key = f.semester || 'Unspecified';
    (bySem[key] = bySem[key] || []).push(f);
  });

  let cards = Object.keys(bySem).length ? Object.keys(bySem).map(sem => `
    <div class="card"><h3>${escapeHtml(sem)}</h3>
      <div class="faculty-grid">${bySem[sem].map(f => renderFacultyCard(f)).join('')}</div>
    </div>`).join('') : '<div class="card"><div class="empty">No additional faculty added yet.</div></div>';

  let formHtml = '';
  if(isAdmin){
    formHtml = `<div class="card"><h3>Add Faculty / Advisor</h3>
      <div class="form-row">
        <input type="text" id="f_name" placeholder="Name">
        <input type="text" id="f_designation" placeholder="Designation (e.g. Associate Professor)">
        <input type="text" id="f_subject" placeholder="Subject">
      </div>
      <div class="form-row">
        <input type="text" id="f_course" placeholder="Course Code (e.g. CSE-101)">
        <input type="email" id="f_email" placeholder="Email">
        <input type="text" id="f_phone" placeholder="Phone Number">
      </div>
      <div class="form-row">
        <input type="text" id="f_semester" placeholder="Semester (e.g. Spring 2026)">
        <div style="flex:1; display:flex; flex-direction:column; gap:2px;">
          <label style="font-size:11px; font-weight:600;">Faculty Photo (Saved in uploads/faculty):</label>
          <input type="file" id="f_image_file" accept="image/*">
        </div>
        <input type="text" id="f_code" placeholder="Google Classroom Code">
      </div>
      <div class="form-row" style="align-items: center; gap: 8px; margin-top: 4px; margin-bottom: 12px;">
        <input type="checkbox" id="f_isAdvisor" style="flex: initial; width: auto; cursor: pointer;">
        <label for="f_isAdvisor" style="font-size: 13px; font-weight: 600; color: var(--green); cursor: pointer;">
          Mark as Class / Batch Advisor
        </label>
      </div>
      <button onclick="addFaculty()">Add Faculty</button>
    </div>`;
  }
  return formHtml + cards;
}

function renderFacultyCard(f) {
  const hasDetails = f.courseCode || f.email || f.phone;
  return `
    <div class="faculty-card">
      ${isAdmin ? `<div class="top-controls">
        <button title="Edit Faculty" onclick="openEditFacultyModal('${f.id}')">✏️</button>
        <button style="color:var(--danger);" title="Delete Faculty" onclick="deleteFaculty('${f.id}')">✕</button>
      </div>` : ''}
      <div class="avatar-wrap">
        <img src="${escapeHtml(f.imageUrl || DEFAULT_AVATAR)}" onerror="this.src='${DEFAULT_AVATAR}'" alt="${escapeHtml(f.name)}">
      </div>
      <h4>${escapeHtml(f.name)}</h4>
      <div class="role">${escapeHtml(f.designation||'')}${f.designation && f.subject ? ' · ' : ''}${escapeHtml(f.subject||'')}</div>
      ${hasDetails ? `
        <div class="details-box">
          ${f.courseCode ? `<div class="detail-item"><strong>Course:</strong> <span>${escapeHtml(f.courseCode)}</span></div>` : ''}
          ${f.email ? `<div class="detail-item"><strong>Email:</strong> <span>${escapeHtml(f.email)}</span></div>` : ''}
          ${f.phone ? `<div class="detail-item"><strong>Phone:</strong> <span>${escapeHtml(f.phone)}</span></div>` : ''}
        </div>
      ` : ''}
      ${f.classroomCode ? `
        <div class="code-pill">
          <span>Classroom: <strong>${escapeHtml(f.classroomCode)}</strong></span>
          <button onclick="copyText('${escapeJs(f.classroomCode)}')">Copy</button>
        </div>
      ` : ''}
    </div>`;
}

async function addFaculty(){
  if(!isAdmin) return;
  const name = val('f_name');
  if(!name){ alert('Please enter a name.'); return; }
  const fileInput = document.getElementById('f_image_file');
  const isAdvisorChecked = document.getElementById('f_isAdvisor') ? document.getElementById('f_isAdvisor').checked : false;

  const json = await api('addFaculty', {
    name, designation: val('f_designation'), subject: val('f_subject'), courseCode: val('f_course'),
    email: val('f_email'), phone: val('f_phone'), semester: val('f_semester'),
    classroomCode: val('f_code'), isAdvisor: isAdvisorChecked
  }, fileInput, 'imageFile');

  if(json) render();
}

function openEditFacultyModal(id) {
  const f = (state.faculty || []).find(item => item.id === id);
  if (!f) return;

  const modalHtml = `
  <div class="modal-overlay">
    <div class="modal-content">
      <h3>Edit Faculty Info</h3>
      <div class="form-row" style="margin-top:12px;">
        <input type="text" id="m_f_name" value="${escapeHtml(f.name)}" placeholder="Name">
        <input type="text" id="m_f_designation" value="${escapeHtml(f.designation)}" placeholder="Designation">
      </div>
      <div class="form-row">
        <input type="text" id="m_f_subject" value="${escapeHtml(f.subject)}" placeholder="Subject">
        <input type="text" id="m_f_course" value="${escapeHtml(f.courseCode)}" placeholder="Course Code">
      </div>
      <div class="form-row">
        <input type="email" id="m_f_email" value="${escapeHtml(f.email)}" placeholder="Email">
        <input type="text" id="m_f_phone" value="${escapeHtml(f.phone)}" placeholder="Phone">
      </div>
      <div class="form-row">
        <input type="text" id="m_f_semester" value="${escapeHtml(f.semester)}" placeholder="Semester">
        <input type="text" id="m_f_code" value="${escapeHtml(f.classroomCode)}" placeholder="Classroom Code">
      </div>
      <div class="form-row">
        <div style="flex:1; display:flex; flex-direction:column; gap:2px;">
          <label style="font-size:11px; font-weight:600;">Update Photo (Saved in uploads/faculty):</label>
          <input type="file" id="m_f_image_file" accept="image/*">
        </div>
      </div>
      <div class="form-row" style="align-items: center; gap: 8px; margin-top: 4px;">
        <input type="checkbox" id="m_f_isAdvisor" ${f.isAdvisor ? 'checked' : ''} style="flex: initial; width: auto; cursor: pointer;">
        <label for="m_f_isAdvisor" style="font-size: 13px; font-weight: 600; color: var(--green); cursor: pointer;">
          Mark as Class / Batch Advisor
        </label>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">
        <button class="secondary" onclick="closeModal()">Cancel</button>
        <button onclick="submitEditFaculty('${f.id}')">Save Changes</button>
      </div>
    </div>
  </div>`;
  document.getElementById('modalContainer').innerHTML = modalHtml;
}

async function submitEditFaculty(id) {
  const fileInput = document.getElementById('m_f_image_file');
  const isAdvisorChecked = document.getElementById('m_f_isAdvisor') ? document.getElementById('m_f_isAdvisor').checked : false;

  const json = await api('editFaculty', {
    id,
    name: val('m_f_name'),
    designation: val('m_f_designation'),
    subject: val('m_f_subject'),
    courseCode: val('m_f_course'),
    email: val('m_f_email'),
    phone: val('m_f_phone'),
    semester: val('m_f_semester'),
    classroomCode: val('m_f_code'),
    isAdvisor: isAdvisorChecked
  }, fileInput, 'imageFile');

  if(json) {
    closeModal();
    render();
  }
}

async function deleteFaculty(id){
  if(!isAdmin) return;
  if(!confirm('Are you sure you want to delete this faculty card?')) return;
  const json = await api('deleteFaculty', {id});
  if(json) render();
}

function copyText(t){ navigator.clipboard.writeText(t).then(()=>{ alert('Copied to clipboard!'); }); }

/* ---------- ATTENDANCE & ROSTER ---------- */
function viewAttendance(){
  const history = (state.attendance || []).slice().sort((a,b)=>b.date.localeCompare(a.date));
  const sortedRoster = (state.roster || []).slice().sort((a,b) => a.sid.localeCompare(b.sid, undefined, {numeric: true}));

  let formHtml = '';
  if(isAdmin){
    formHtml = `
    <div class="card">
      <h3>Add Student to Roster</h3>
      <div class="form-row">
        <input type="text" id="st_id" placeholder="Student ID (e.g. 101)">
        <input type="text" id="st_name" placeholder="Student Name">
        <button onclick="addRosterStudent()">Add Student</button>
      </div>
    </div>

    <div class="card">
      <h3>Tap to Mark Attendance</h3>
      <p class="hint">Tap on any student box below to mark them Present/Absent. Students are ordered serially by ID.</p>
      <div class="form-row">
        <input type="date" id="a_date" value="${escapeHtml(attendanceDateInput)}" onchange="updateAttendanceInputs()" oninput="updateAttendanceInputs()">
        <input type="text" id="a_subject" value="${escapeHtml(attendanceSubjectInput)}" placeholder="Subject Name" oninput="updateAttendanceInputs()">
      </div>
      <div class="student-grid">
        ${sortedRoster.length ? sortedRoster.map(s => {
          const isPresent = presentSet.has(s.sid);
          return `
            <div class="student-card ${isPresent ? 'present' : ''}" onclick="toggleAttendance('${escapeJs(s.sid)}')">
              ${isAdmin ? `<button class="del-btn" onclick="event.stopPropagation(); removeRosterStudent('${escapeJs(s.sid)}')">×</button>` : ''}
              <div class="sid">${escapeHtml(s.sid)}</div>
              <div class="sname">${escapeHtml(s.name)}</div>
              <span class="status-pill">${isPresent ? 'Present' : 'Absent'}</span>
            </div>`;
        }).join('') : '<div class="empty">No students in roster. Add students above to begin taking attendance.</div>'}
      </div>
      <h3 style="margin-top:18px;">Live Attendance Preview</h3>
      <div class="att-preview" id="attPreview"></div>
      <div style="margin-top:10px; display:flex; gap:8px;">
        <button onclick="copyAttendance()">Copy Attendance Text</button>
        <button class="secondary" onclick="saveAttendance()">Save to History</button>
      </div>
    </div>`;
  }

  return `${formHtml}
  <div class="card"><h3>Attendance History</h3>
    ${history.length ? history.map(a=>`
      <div class="period-row" style="display:flex; gap:8px; align-items:center; padding:6px 0; border-bottom:1px dashed var(--line);">
        <span class="time mono" style="width:100px;">${a.date}</span>
        <span class="subj" style="flex:1; font-weight:600;">${escapeHtml(a.subject)}</span>
        <span class="meta">${a.totalPresent} present</span>
        <button class="small secondary" onclick="copyText(\`${escapeJs(buildAttendanceText(a))}\`)">Copy</button>
        ${isAdmin ? `<button class="small danger" onclick="deleteAttendance('${a.id}')">Delete</button>` : ''}
      </div>`).join('') : '<div class="empty">No attendance records yet.</div>'}
  </div>`;
}

function updateAttendanceInputs(){
  const dateEl = document.getElementById('a_date');
  const subjEl = document.getElementById('a_subject');
  if(dateEl) attendanceDateInput = dateEl.value;
  if(subjEl) attendanceSubjectInput = subjEl.value;
  livePreview();
}

async function addRosterStudent(){
  if(!isAdmin) return;
  const sid = val('st_id'), name = val('st_name');
  if(!sid || !name){ alert('Enter both Student ID and Name.'); return; }
  const json = await api('addRosterStudent', {sid, name});
  if(json) render();
}
async function removeRosterStudent(sid){
  if(!isAdmin) return;
  const json = await api('removeRosterStudent', {sid});
  if(json){ presentSet.delete(sid); render(); }
}
function toggleAttendance(sid){
  updateAttendanceInputs();
  if(presentSet.has(sid)) presentSet.delete(sid); else presentSet.add(sid);
  render();
}
function buildAttendanceText(a){
  const lines = (a.students || []).map((s,i)=>`${i+1}. ${s.sid} - ${s.name}`).join('\n');
  return `Date: ${a.date}\nSubject: ${a.subject}\nAttendance:\n${lines}\nTotal Present: ${a.totalPresent}`;
}
function livePreview(){
  const date = attendanceDateInput;
  const subject = attendanceSubjectInput;
  const validStudents = (state.roster || []).filter(s => presentSet.has(s.sid)).sort((a,b) => a.sid.localeCompare(b.sid, undefined, {numeric: true}));
  const preview = document.getElementById('attPreview');
  if(preview) preview.textContent = buildAttendanceText({date, subject, students: validStudents, totalPresent: validStudents.length});
}
function copyAttendance(){ const t = document.getElementById('attPreview').textContent; copyText(t); }

async function saveAttendance(){
  if(!isAdmin) return;
  updateAttendanceInputs();
  const date = attendanceDateInput, subject = attendanceSubjectInput;
  const sids = Array.from(presentSet);
  if(!subject || !sids.length){ alert('Select a subject and tap at least one student card to mark them Present.'); return; }
  const json = await api('saveAttendance', {date, subject, sids});
  if(json){ presentSet.clear(); attendanceSubjectInput = ''; render(); }
}
async function deleteAttendance(id){
  if(!isAdmin) return;
  const json = await api('deleteAttendance', {id});
  if(json) render();
}

/* ---------- TASKS ---------- */
function taskTable(list){
  const today = new Date(new Date().toDateString());
  const rows = list.slice().sort((a,b)=>a.deadline.localeCompare(b.deadline)).map(t=>{
    const d = new Date(t.deadline);
    const diffDays = Math.round((d-today)/86400000);
    let rowClass = t.status==='done' ? 'done' : (diffDays<0 ? 'overdue' : (diffDays<=1 ? 'soon':''));
    const isPinned = t.isPinned !== false;

    return `<tr class="${rowClass}">
      <td>${escapeHtml(t.title)}</td>
      <td>${escapeHtml(t.subject||'')}</td>
      <td>${escapeHtml(t.note || '—')}</td>
      <td>${t.deadline}</td>
      <td><span class="badge ${t.status}">${t.status}</span></td>
      ${isAdmin ? `<td>
        <button class="small secondary" onclick="openEditTaskModal('${t.id}')">Edit</button>
        <button class="small secondary" onclick="toggleTask('${t.id}')">${t.status==='done'?'Reopen':'Mark done'}</button>
        <button class="small ${isPinned ? 'secondary' : ''}" onclick="togglePinTask('${t.id}')">${isPinned ? '📌 Pinned' : '📍 Unpinned'}</button>
        <button class="small danger" onclick="deleteTask('${t.id}')">Delete</button>
      </td>` : ''}
    </tr>`;
  }).join('');

  return `<div class="table-responsive"><table><thead><tr>
    <th>Task</th><th>Subject</th><th>Note</th><th>Deadline</th><th>Status</th>
    ${isAdmin ? '<th>Admin Controls</th>' : ''}
    </tr></thead><tbody>${rows}</tbody></table></div>`;
}

function viewTasks(){
  let formHtml = '';
  if(isAdmin){
    formHtml = `<div class="card"><h3>Assign Task</h3>
      <div class="form-row">
        <input type="text" id="t_title" placeholder="Task title">
        <input type="text" id="t_subject" placeholder="Subject">
      </div>
      <div class="form-row">
        <input type="date" id="t_deadline">
        <input type="text" id="t_note" placeholder="Task Note / Instructions">
      </div>
      <button onclick="addTask()">Add Task</button>
    </div>`;
  }

  const todayStr = new Date().toISOString().slice(0, 10);
  const visibleTasks = (state.tasks || []).filter(t => isAdmin || (t.status === 'pending' && t.deadline >= todayStr));

  return `${formHtml}<div class="card"><h3>All Tasks</h3>${visibleTasks.length ? taskTable(visibleTasks) : '<div class="empty">No active tasks.</div>'}</div>`;
}

async function addTask(){
  if(!isAdmin) return;
  const title=val('t_title'), subject=val('t_subject'), deadline=val('t_deadline'), note=val('t_note');
  if(!title||!deadline){ alert('Please fill task title and deadline.'); return; }
  const json = await api('addTask', {title, subject, deadline, note});
  if(json) render();
}

function openEditTaskModal(id) {
  const t = (state.tasks || []).find(item => item.id === id);
  if (!t) return;

  const modalHtml = `
  <div class="modal-overlay">
    <div class="modal-content">
      <h3>Edit Task</h3>
      <div class="form-row" style="margin-top:12px;">
        <input type="text" id="m_t_title" value="${escapeHtml(t.title)}" placeholder="Task title">
        <input type="text" id="m_t_subject" value="${escapeHtml(t.subject || '')}" placeholder="Subject">
      </div>
      <div class="form-row">
        <input type="date" id="m_t_deadline" value="${t.deadline}">
        <input type="text" id="m_t_note" value="${escapeHtml(t.note || '')}" placeholder="Task Note / Instructions">
      </div>
      <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">
        <button class="secondary" onclick="closeModal()">Cancel</button>
        <button onclick="submitEditTask('${t.id}')">Save Changes</button>
      </div>
    </div>
  </div>`;
  document.getElementById('modalContainer').innerHTML = modalHtml;
}

async function submitEditTask(id) {
  const title = val('m_t_title'), subject = val('m_t_subject'), deadline = val('m_t_deadline'), note = val('m_t_note');
  if (!title || !deadline) { alert('Please fill task title and deadline.'); return; }
  const json = await api('editTask', { id, title, subject, deadline, note });
  if (json) {
    closeModal();
    render();
  }
}

async function toggleTask(id){
  if(!isAdmin) return;
  const json = await api('toggleTask', {id});
  if(json) render();
}
async function togglePinTask(id){
  if(!isAdmin) return;
  const json = await api('togglePinTask', {id});
  if(json) render();
}
async function deleteTask(id){
  if(!isAdmin) return;
  const json = await api('deleteTask', {id});
  if(json) render();
}

/* ---------- SETTINGS / AUTH ---------- */
function viewSettings(){
  if(!isAdmin){
    return `<div class="card">
      <h3>Admin Access</h3>
      <form class="form-row" onsubmit="event.preventDefault(); login();">
        <input type="password" id="loginPass" placeholder="Admin Password">
        <button type="submit">Login as Admin</button>
      </form>
    </div>`;
  }

  return `<div class="card">
    <h3>Class / Brand Settings</h3>
    <div class="form-row">
      <input type="text" id="meta_className" value="${escapeHtml(state.meta.className || '')}" placeholder="Class Name (e.g. CSE60F)">
      <input type="text" id="meta_semester" value="${escapeHtml(state.meta.semester || '')}" placeholder="Semester (e.g. Summer 2026)">
    </div>
    <div class="form-row">
      <div style="flex:1; display:flex; flex-direction:column; gap:2px;">
        <label style="font-size:11px; font-weight:600;">Brand / Header Logo (Saved in uploads/assets):</label>
        <input type="file" id="meta_brand_file" accept="image/*">
      </div>
    </div>
    <button onclick="saveMeta()">Save Header Settings</button>
  </div>

  <div class="card">
    <h3>Change Admin Password</h3>
    <div class="form-row">
      <input type="password" id="p_old" placeholder="Old Password">
      <input type="password" id="p_new" placeholder="New Password">
      <input type="password" id="p_confirm" placeholder="Confirm New Password">
    </div>
    <button onclick="changePassword()">Change Password</button>
  </div>

  <div class="card">
    <h3>Data Management</h3>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <button onclick="exportData()">Export Database JSON</button>
      <button class="secondary" onclick="document.getElementById('importFile').click()">Import Database JSON</button>
      <input type="file" id="importFile" style="display:none;" accept=".json" onchange="importData(event)">
    </div>
  </div>`;
}

async function login(){
  const password = val('loginPass');
  if(!password) return;
  const json = await api('login', {password});
  if(json){
    isAdmin = json.isAdmin;
    render();
  }
}

async function logout(){
  const json = await api('logout');
  if(json){
    isAdmin = json.isAdmin;
    render();
  }
}

async function saveMeta(){
  if(!isAdmin) return;
  const className = val('meta_className');
  const semester = val('meta_semester');
  const fileInput = document.getElementById('meta_brand_file');
  const json = await api('saveMeta', {className, semester}, fileInput, 'brandImageFile');
  if(json) render();
}

async function changePassword(){
  if(!isAdmin) return;
  const oldPassword = val('p_old');
  const newPassword = val('p_new');
  const confirmPassword = val('p_confirm');
  if(!oldPassword || !newPassword || !confirmPassword){
    alert('Please fill in all password fields.');
    return;
  }
  const json = await api('changePassword', {oldPassword, newPassword, confirmPassword});
  if(json){
    alert('Password changed successfully.');
    render();
  }
}

function exportData(){
  const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(state, null, 2));
  const downloadAnchor = document.createElement('a');
  downloadAnchor.setAttribute("href", dataStr);
  downloadAnchor.setAttribute("download", `database_${new Date().toISOString().slice(0,10)}.json`);
  document.body.appendChild(downloadAnchor);
  downloadAnchor.click();
  downloadAnchor.remove();
}

function importData(event){
  const file = event.target.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = async function(e){
    try{
      const data = JSON.parse(e.target.result);
      const json = await api('importData', {data});
      if(json){
        alert('Data imported successfully.');
        render();
      }
    }catch(err){
      alert('Invalid JSON file format.');
    }
  };
  reader.readAsText(file);
}

render();
</script>
</body>
</html>