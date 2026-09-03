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
#graph {
  width:100%; height:620px;
  background:
    radial-gradient(900px 500px at 50% 40%, rgba(63,185,80,.06), transparent 60%),
    radial-gradient(circle at center, rgba(255,255,255,.025) 1px, transparent 1px) 0 0 / 26px 26px,
    #0b0f17;
}

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
    <p>How the key ideas in <strong style="color:var(--text)"><?= $safe_name ?></strong> connect &nbsp;·&nbsp; click a concept to focus &nbsp;·&nbsp; drag &amp; scroll to explore</p>
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

function hexToRgba(hex, a) {
  const n = parseInt(hex.slice(1), 16);
  return `rgba(${(n>>16)&255},${(n>>8)&255},${n&255},${a})`;
}

function renderGraph(data) {
  const host = document.getElementById('graphHost');
  host.innerHTML = '<div id="graph"></div>';

  const groups = [...new Set(data.nodes.map(n => n.group || 'Concept'))];
  const colorOf = g => PALETTE[groups.indexOf(g) % PALETTE.length];

  // Degree centrality → bigger, brighter nodes for more-connected concepts
  const degree = {};
  data.nodes.forEach(n => degree[n.id] = 0);
  (data.edges || []).forEach(e => { degree[e.from] = (degree[e.from]||0)+1; degree[e.to] = (degree[e.to]||0)+1; });
  const maxDeg = Math.max(1, ...Object.values(degree));

  const nodes = data.nodes.map(n => {
    const g    = n.group || 'Concept';
    const base = colorOf(g);
    const deg  = degree[n.id] || 0;
    const size = 14 + (deg / maxDeg) * 26;          // 14 → 40 by importance
    return {
      id: n.id,
      label: n.id,
      group: g,
      title: `${n.id}  ·  ${g}  ·  ${deg} connection${deg===1?'':'s'}`,
      value: deg + 1,
      size: size,
      shape: 'dot',
      borderWidth: 2,
      color: {
        background: base,
        border: hexToRgba(base, .35),
        highlight: { background: base, border: '#ffffff' },
        hover:     { background: base, border: '#ffffff' }
      },
      shadow: { enabled: true, color: hexToRgba(base, .55), size: 18 + (deg/maxDeg)*22, x: 0, y: 0 },
      font: {
        color: '#e7edf4', face: 'Inter',
        size: 13 + (deg / maxDeg) * 6, strokeWidth: 4, strokeColor: '#070b12',
        vadjust: -2, multi: false
      }
    };
  });

  const edges = (data.edges || []).map(e => ({
    from: e.from, to: e.to, label: e.label || '',
    font: { color: 'rgba(139,149,167,.5)', size: 9.5, face: 'Inter', strokeWidth: 3, strokeColor: '#070b12', align: 'horizontal' },
    color: { color: 'rgba(255,255,255,.1)', highlight: '#3fb950', hover: '#3fb950', opacity: 1 },
    width: 1, selectionWidth: 2.5,
    arrows: { to: { enabled: true, scaleFactor: 0.4, type: 'arrow' } },
    smooth: { type: 'continuous', roundness: 0.15 }
  }));

  const nodesDS = new vis.DataSet(nodes);
  const edgesDS = new vis.DataSet(edges);

  const network = new vis.Network(document.getElementById('graph'),
    { nodes: nodesDS, edges: edgesDS },
    {
      physics: {
        solver: 'forceAtlas2Based',
        forceAtlas2Based: { gravitationalConstant: -110, centralGravity: 0.008, springLength: 210, springConstant: 0.08, damping: 0.6, avoidOverlap: 0.6 },
        stabilization: { iterations: 280 },
        timestep: 0.4
      },
      interaction: { hover: true, tooltipDelay: 120, navigationButtons: false, keyboard: false },
      nodes: { scaling: { min: 14, max: 42 } }
    }
  );

  // Centre & zoom the whole graph nicely once it settles
  network.once('stabilizationIterationsDone', () => network.fit({ animation: { duration: 600 } }));

  // ── Click a node → spotlight its neighbourhood, dim the rest ──
  const allNodeIds = nodes.map(n => n.id);
  function spotlight(centerId) {
    const connected = new Set([centerId]);
    edgesDS.forEach(e => { if (e.from === centerId) connected.add(e.to); if (e.to === centerId) connected.add(e.from); });
    nodesDS.update(nodes.map(n => {
      const on = connected.has(n.id);
      return { id: n.id, opacity: on ? 1 : 0.18,
               font: { color: on ? '#e7edf4' : 'rgba(231,237,244,.25)', face:'Inter',
                       size: n.font.size, strokeWidth: 4, strokeColor: '#070b12' } };
    }));
    edgesDS.update(edges.map(e => {
      const on = e.from === centerId || e.to === centerId;
      return { id: e.id, color: { color: on ? '#3fb950' : 'rgba(255,255,255,.04)' },
               font: { color: on ? '#9aa6b8' : 'rgba(125,135,153,.15)', size:10.5, strokeWidth:4, strokeColor:'#070b12' } };
    }));
  }
  function resetSpotlight() {
    nodesDS.update(nodes.map(n => ({ id: n.id, opacity: 1, font: n.font })));
    edgesDS.update(edges.map(e => ({ id: e.id, color: e.color, font: e.font })));
  }
  network.on('selectNode', p => spotlight(p.nodes[0]));
  network.on('deselectNode', resetSpotlight);
  network.on('click', p => { if (!p.nodes.length) resetSpotlight(); });

  // Legend
  const legend = document.getElementById('legend');
  legend.style.display = 'flex';
  legend.innerHTML =
    `<span class="legend-item" style="color:var(--text-dim)">${data.nodes.length} concepts · ${(data.edges||[]).length} links · click a node to focus</span>` +
    groups.map(g => `<span class="legend-item"><span class="legend-dot" style="background:${colorOf(g)}"></span>${g.replace(/</g,'&lt;')}</span>`).join('');
}

loadMap();
</script>
</body>
</html>
