<?php
$page_title = 'Analytics';
$active_page = 'analytics';
require_once 'includes/auth_middleware.php';
require_permission('editor');
$db = getDB();

// Top Posts
$top_posts = $db->query("SELECT title, views, slug FROM posts ORDER BY views DESC LIMIT 5")->fetchAll();
// Posts by status
$p_draft     = $db->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetchColumn();
$p_published = $db->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
$p_scheduled = $db->query("SELECT COUNT(*) FROM posts WHERE status='scheduled'")->fetchColumn();
// User roles
$a_admin  = $db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$a_editor = $db->query("SELECT COUNT(*) FROM users WHERE role='editor'")->fetchColumn();
$a_author = $db->query("SELECT COUNT(*) FROM users WHERE role='author'")->fetchColumn();
// Comments
$c_pending  = $db->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn();
$c_approved = $db->query("SELECT COUNT(*) FROM comments WHERE status='approved'")->fetchColumn();
$c_spam     = $db->query("SELECT COUNT(*) FROM comments WHERE status='spam'")->fetchColumn();

include_once 'partials/header.php';
?>
<div style="margin-bottom:30px;">
    <h1 style="font-size:24px;font-weight:800;">Analytics</h1>
    <p style="color:var(--text-muted);">Traffic overview, content performance, and user engagement.</p>
</div>

<!-- Chart Row 1 -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:30px;margin-bottom:30px;">
    <div class="card">
        <h3 style="font-weight:700;margin-bottom:5px;">Traffic Overview</h3>
        <p style="color:var(--text-muted);font-size:13px;margin-bottom:20px;">Visitors & page views over the last 30 days</p>
        <canvas id="trafficChart" height="120"></canvas>
    </div>
    <div class="card">
        <h3 style="font-weight:700;margin-bottom:5px;">Post Status</h3>
        <p style="color:var(--text-muted);font-size:13px;margin-bottom:20px;">Distribution of draft vs live vs scheduled</p>
        <canvas id="statusChart" height="200"></canvas>
    </div>
</div>

<!-- Chart Row 2 -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-bottom:30px;">
    <div class="card">
        <h3 style="font-weight:700;margin-bottom:5px;">User Roles</h3>
        <p style="color:var(--text-muted);font-size:13px;margin-bottom:20px;">Breakdown of admins, editors, and authors</p>
        <canvas id="rolesChart" height="200"></canvas>
    </div>
    <div class="card">
        <h3 style="font-weight:700;margin-bottom:5px;">Comment Moderation</h3>
        <p style="color:var(--text-muted);font-size:13px;margin-bottom:20px;">Pending vs approved vs spam</p>
        <canvas id="commentsChart" height="200"></canvas>
    </div>
</div>

<!-- Top Performing Content -->
<div class="card">
    <h3 style="font-weight:700;margin-bottom:20px;">Top Performing Posts</h3>
    <?php foreach ($top_posts as $i => $p): ?>
    <div style="display:flex;align-items:center;gap:20px;padding:15px 0;<?php echo $i < count($top_posts)-1 ? 'border-bottom:1px solid var(--border);' : ''; ?>">
        <span style="font-size:24px;font-weight:800;color:var(--border);width:30px;"><?php echo $i+1; ?></span>
        <div style="flex:1;">
            <div style="font-weight:700;"><?php echo $p['title']; ?></div>
            <div style="font-size:12px;color:var(--text-muted);"><?php echo $p['slug']; ?></div>
        </div>
        <div style="font-size:20px;font-weight:800;color:var(--primary);"><?php echo number_format($p['views']); ?> <span style="font-size:12px;font-weight:500;color:var(--text-muted);">views</span></div>
        <div style="flex:1;height:8px;background:var(--bg-body);border-radius:4px;overflow:hidden;">
            <?php $max = max(array_column($top_posts,'views')) ?: 1; ?>
            <div style="width:<?php echo ($p['views']/$max*100); ?>%;height:100%;background:var(--primary);"></div>
        </div>
    </div>
    <?php endforeach; if (empty($top_posts)): ?>
    <p style="color:var(--text-muted);text-align:center;padding:40px;">No posts data yet.</p>
    <?php endif; ?>
</div>

<script>
// Traffic line chart (mock data – replace with real data from DB as needed)
new Chart(document.getElementById('trafficChart'), {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Visitors',
            data: [1200,1900,1700,2500,2200,3100,2800,3500,3200,4000,3700,4500],
            borderColor:'#4361ee', backgroundColor:'rgba(67,97,238,.1)', fill:true, tension:0.4
        }]
    },
    options: { plugins:{legend:{display:false}}, scales:{ y:{grid:{display:false}}, x:{grid:{display:false}} } }
});
// Status doughnut
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Published','Draft','Scheduled'],
        datasets: [{ data: [<?php echo $p_published; ?>,<?php echo $p_draft; ?>,<?php echo $p_scheduled; ?>], backgroundColor:['#2ec4b6','#808191','#4361ee'], borderWidth:0 }]
    },
    options: { plugins:{ legend:{ position:'bottom' } }, cutout:'65%' }
});
// Roles bar
new Chart(document.getElementById('rolesChart'), {
    type: 'bar',
    data: {
        labels:['Admin','Editor','Author'],
        datasets:[{ data:[<?php echo $a_admin; ?>,<?php echo $a_editor; ?>,<?php echo $a_author; ?>], backgroundColor:['#4361ee','#2ec4b6','#ff9f1c'], borderRadius:8 }]
    },
    options:{ plugins:{legend:{display:false}}, scales:{y:{grid:{display:false},ticks:{precision:0}},x:{grid:{display:false}}} }
});
// Comments bar
new Chart(document.getElementById('commentsChart'), {
    type: 'bar',
    data: {
        labels:['Pending','Approved','Spam'],
        datasets:[{ data:[<?php echo $c_pending; ?>,<?php echo $c_approved; ?>,<?php echo $c_spam; ?>], backgroundColor:['#ff9f1c','#2ec4b6','#e63946'], borderRadius:8 }]
    },
    options:{ plugins:{legend:{display:false}}, scales:{y:{grid:{display:false},ticks:{precision:0}},x:{grid:{display:false}}} }
});
</script>
<?php include_once 'partials/footer.php'; ?>
