<?php
session_start();
if (!isset($_SESSION["user_name"])) { header("Location: authentication.php"); exit(); }
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: dashboard.php"); exit(); }
$stmt = $con->prepare("SELECT file_name, file_path FROM pdf_uploads WHERE id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if (!$row) { header("Location: dashboard.php"); exit(); }
$safe_name = htmlspecialchars(clean_name($row['file_name']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie – Concept Map</title>
<script src="https://unpkg.com/vis-network@9.1.9/standalone/umd/vis-network.min.js"></script>
<?php include 'theme.php'; ?>
<style>
.page-wrapper { max-width: 1200px; margin: auto; }
.breadcrumb { font-size:13px; color:var(--text-dim); margin-bottom:18px; }
.breadcrumb a { color:var(--text-muted); } .breadcrumb a:hover { color:var(--text); }

.map-head { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
.map-head h2 { font-size:18px; font-weight:700; color:var(--text); }
.map-head p { font-size:13px; color:var(--text-muted); margin-top:2px; }

.graph-card { padding:0; overflow:hidden; }
#graph { width:100%; height:600px; background:
  radial-gradient(700px 400px at 30% 20%, rgba(63,185,80,.05), transparent 60%); }

.legend { display:flex; flex-wrap:wrap; gap:10px; padding:14px 20px; border-top:1px solid var(--border); }
.legend-item { display:flex; align-items:center; gap:7px; font-size:12.5px; color:var(--text-muted); }
.legend-dot { width:12px; height:12px; border-radius:50%; }

.map-loading, .map-error {
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  height:600px; color:var(--text-muted); gap:14px; text-align:center; padding:20px;
}
.spinner { width:40px; height:40px; border:3px solid var(--border); border-top-color:var(--accent);
  border-radius:50%; animation:spin 1s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
.map-error { color:#ff7b72; }
</style>
</head>
<body>
<div class="page-wrapper">
  <?php include "navbar.php"; ?>

  <div class="breadcrumb">
    <a href="dashboard.php">Dashboard</a> &nbsp;›&nbsp;
    <a href="docdetail.php?id=<?= $id ?>"><?= $safe_name ?></a> &nbsp;›&nbsp;
    <span>Concept Map</span>
  </div>

  <div class="map-head">
    <div>
      <h2>🧩 Concept Map</h2>
      <p>How the key ideas in <strong style="color:var(--text)"><?= $safe_name ?></strong> connect. Drag nodes · scroll to zoom.</p>
    </div>
    <button class="sg-btn sg-btn-ghost" onclick="loadMap()">↻ Regenerate</button>
  </div>

  <div class="sg-card-flat graph-card">
    <div id="graphHost">
      <div class="map-loading" id="mapLoading">
        <div class="spinner"></div>
        <div>Analysing document and mapping concepts…</div>
      </div>
    </div>
    <div class="legend" id="legend" style="display:none;"></div>
  </div>
</div>

<script>
const docId = <?= intval($id) ?>;
const PALETTE = ['#3fb950','#58a6ff','#d2a8ff','#e3b341','#ff7b72','#39c5cf','#ff9bce','#a5d6ff'];

async function loadMap() {
  const host = document.getElementById('graphHost');
  document.getElementById('legend').style.display = 'none';
  host.innerHTML = '<div class="map-loading" id="mapLoading"><div class="spinner"></div><div>Analysing document and mapping concepts…</div></div>';

  try {
    const res  = await fetch('conceptmap_bridge.php?id=' + docId);
    const data = await res.json();
    if (data.error)      { showErr(data.error); return; }
    if (!data.nodes || data.nodes.length < 2) { showErr('Not enough concepts found to build a map.'); return; }
    renderGraph(data);
    showToast('Concept map ready!', 'success');
  } catch (e) { showErr(e.message); }
}

function showErr(msg) {
  document.getElementById('graphHost').innerHTML =
    `<div class="map-error">⚠️ ${msg.replace(/</g,'&lt;')}</div>`;
}

function renderGraph(data) {
  const host = document.getElementById('graphHost');
  host.innerHTML = '<div id="graph"></div>';

  // Map groups → colors
  const groups = [...new Set(data.nodes.map(n => n.group || 'Concept'))];
  const colorOf = g => PALETTE[groups.indexOf(g) % PALETTE.length];

  const nodes = data.nodes.map((n, i) => ({
    id: n.id,
    label: n.id,
    group: n.group || 'Concept',
    color: { background: colorOf(n.group || 'Concept'), border: 'rgba(255,255,255,.25)',
             highlight: { background: colorOf(n.group || 'Concept'), border: '#fff' } },
    font: { color: '#0a0e14', face: 'Inter', size: 15, strokeWidth: 0 },
    shape: 'dot',
    size: 18,
    borderWidth: 2
  }));

  const edges = (data.edges || []).map(e => ({
    from: e.from, to: e.to, label: e.label || '',
    font: { color: '#8b95a7', size: 11, face: 'Inter', strokeWidth: 4, strokeColor: '#0a0e14' },
    color: { color: 'rgba(255,255,255,.18)', highlight: '#3fb950' },
    arrows: { to: { enabled: true, scaleFactor: 0.5 } },
    smooth: { type: 'continuous' }
  }));

  const network = new vis.Network(document.getElementById('graph'),
    { nodes: new vis.DataSet(nodes), edges: new vis.DataSet(edges) },
    {
      physics: { stabilization: true, barnesHut: { gravitationalConstant: -8000, springLength: 150 } },
      interaction: { hover: true, tooltipDelay: 120 },
      nodes: { scaling: { min: 14, max: 30 } }
    }
  );

  // Legend
  const legend = document.getElementById('legend');
  legend.style.display = 'flex';
  legend.innerHTML = groups.map(g =>
    `<span class="legend-item"><span class="legend-dot" style="background:${colorOf(g)}"></span>${g.replace(/</g,'&lt;')}</span>`
  ).join('');
}

loadMap();
</script>
</body>
</html>
