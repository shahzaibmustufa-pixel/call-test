<?php
$page_title = 'Dashboard';
$active_page = 'dashboard';
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$db = getDB();

// Fetch basic stats
$total_posts = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_categories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$total_views = $db->query("SELECT SUM(views) FROM posts")->fetchColumn() ?? 0;

// Fetch recent posts
$recent_posts = $db->query("SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 5")->fetchAll();

include_once 'partials/header.php';
?>

<div class="dashboard-header" style="margin-bottom: 30px;">
    <h1 style="font-size: 24px; font-weight: 800;">Overview</h1>
    <p style="color: var(--text-muted);">Welcome back! Here's what's happening with your site today.</p>
</div>

<?php displayFlash(); ?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon" style="background: rgba(67, 97, 238, 0.1); color: var(--primary);">
            <i class="fas fa-file-alt"></i>
        </div>
        <div>
            <div style="font-size: 14px; color: var(--text-muted); font-weight: 500;">Total Posts</div>
            <div style="font-size: 24px; font-weight: 800;"><?php echo $total_posts; ?></div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background: rgba(46, 196, 182, 0.1); color: var(--success);">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div style="font-size: 14px; color: var(--text-muted); font-weight: 500;">Total Users</div>
            <div style="font-size: 24px; font-weight: 800;"><?php echo $total_users; ?></div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background: rgba(255, 159, 28, 0.1); color: var(--warning);">
            <i class="fas fa-folder"></i>
        </div>
        <div>
            <div style="font-size: 14px; color: var(--text-muted); font-weight: 500;">Categories</div>
            <div style="font-size: 24px; font-weight: 800;"><?php echo $total_categories; ?></div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background: rgba(76, 201, 240, 0.1); color: var(--info);">
            <i class="fas fa-eye"></i>
        </div>
        <div>
            <div style="font-size: 14px; color: var(--text-muted); font-weight: 500;">Total Views</div>
            <div style="font-size: 24px; font-weight: 800;"><?php echo number_format($total_views); ?></div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 40px;">
    <!-- Chart Card -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h3 style="font-weight: 700;">Traffic Analytics</h3>
            <select class="form-control" style="width: auto; padding: 5px 15px;">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
            </select>
        </div>
        <canvas id="trafficChart" height="150"></canvas>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <h3 style="font-weight: 700; margin-bottom: 25px;">Quick Actions</h3>
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <a href="post-edit.php" class="btn btn-primary" style="justify-content: flex-start;">
                <i class="fas fa-plus"></i> Create New Post
            </a>
            <a href="users.php" class="btn" style="justify-content: flex-start; background: var(--bg-body); border: 1px solid var(--border);">
                <i class="fas fa-user-plus"></i> Add New User
            </a>
            <a href="settings.php" class="btn" style="justify-content: flex-start; background: var(--bg-body); border: 1px solid var(--border);">
                <i class="fas fa-cog"></i> Site Settings
            </a>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
            <h4 style="font-size: 14px; color: var(--text-muted); margin-bottom: 15px;">Storage Usage</h4>
            <div style="height: 8px; background: var(--bg-body); border-radius: 4px; overflow: hidden; margin-bottom: 10px;">
                <div style="width: 45%; height: 100%; background: var(--primary);"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600;">
                <span>4.5 GB of 10 GB</span>
                <span>45%</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Table -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h3 style="font-weight: 700;">Recent Posts</h3>
        <a href="posts.php" style="color: var(--primary); font-size: 14px; font-weight: 600;">View All</a>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Post Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Views</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_posts as $post): ?>
                <tr>
                    <td>
                        <div style="font-weight: 600; color: var(--text-main);"><?php echo $post['title']; ?></div>
                        <div style="font-size: 12px; color: var(--text-muted);"><?php echo $post['slug']; ?></div>
                    </td>
                    <td><span style="padding: 5px 12px; background: var(--bg-body); border-radius: 20px; font-size: 12px; font-weight: 600;"><?php echo $post['category_name'] ?: 'Uncategorized'; ?></span></td>
                    <td>
                        <span style="padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; <?php echo ($post['status'] == 'published') ? 'background: rgba(46, 196, 182, 0.1); color: var(--success);' : 'background: rgba(128, 129, 145, 0.1); color: var(--text-muted);'; ?>">
                            <?php echo ucfirst($post['status']); ?>
                        </span>
                    </td>
                    <td style="font-size: 14px; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                    <td style="font-weight: 700;"><?php echo number_format($post['views']); ?></td>
                </tr>
                <?php endforeach; if (empty($recent_posts)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">No posts found. Start by creating your first post!</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Traffic Chart
    const ctx = document.getElementById('trafficChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Visitors',
                data: [1200, 1900, 1500, 2500, 2200, 3000, 2800],
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#4361ee'
            }, {
                label: 'Views',
                data: [3500, 4200, 3800, 5000, 4800, 6000, 5800],
                borderColor: '#4cc9f0',
                backgroundColor: 'transparent',
                fill: false,
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { grid: { display: false }, ticks: { font: { size: 10 } } },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
</script>

<?php include_once 'partials/footer.php'; ?>