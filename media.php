<?php
$page_title = 'Media Library';
$active_page = 'media';
require_once 'includes/auth_middleware.php';
require_permission('author');
$db = getDB();

// Handle delete
if (isset($_GET['delete'])) {
    $mid = (int)$_GET['delete'];
    $row = $db->prepare("SELECT file_path FROM media WHERE id = ?");
    $row->execute([$mid]);
    $f = $row->fetch();
    if ($f && file_exists('../' . $f['file_path'])) {
        unlink('../' . $f['file_path']);
    }
    $db->prepare("DELETE FROM media WHERE id = ?")->execute([$mid]);
    redirect('media.php', 'File deleted.');
}

// Handle AJAX upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file'])) {
    header('Content-Type: application/json');
    $file = $_FILES['file'];
    $allowed = ['image/jpeg','image/png','image/gif','image/webp','video/mp4','application/pdf'];
    if (!in_array($file['type'], $allowed)) {
        echo json_encode(['success'=>false,'message'=>'File type not allowed.']); exit;
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success'=>false,'message'=>'File too large (max 10 MB).']); exit;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $dest_name = time() . '_' . uniqid() . '.' . $ext;
    $dest_path = 'uploads/' . $dest_name;
    if (!is_dir('../uploads')) mkdir('../uploads', 0755, true);
    if (move_uploaded_file($file['tmp_name'], '../' . $dest_path)) {
        $db->prepare("INSERT INTO media (user_id, file_name, file_path, file_type, file_size) VALUES (?,?,?,?,?)")
           ->execute([$_SESSION['user_id'], $file['name'], $dest_path, $file['type'], $file['size']]);
        $id = $db->lastInsertId();
        echo json_encode(['success'=>true,'id'=>$id,'path'=>$dest_path,'type'=>$file['type'],'name'=>$file['name']]); exit;
    }
    echo json_encode(['success'=>false,'message'=>'Upload failed.']); exit;
}

// Pagination
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 24;
$offset = ($page - 1) * $limit;
$total = $db->query("SELECT COUNT(*) FROM media")->fetchColumn();
$media = $db->prepare("SELECT * FROM media ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$media->execute(); $media = $media->fetchAll();
$pages = ceil($total / $limit);

include_once 'partials/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
    <div>
        <h1 style="font-size:24px;font-weight:800;">Media Library</h1>
        <p style="color:var(--text-muted);">Upload and manage images, videos, and documents.</p>
    </div>
    <button onclick="document.getElementById('file-input').click()" class="btn btn-primary">
        <i class="fas fa-upload"></i> Upload Files
    </button>
    <input type="file" id="file-input" multiple accept="image/*,video/mp4,application/pdf" style="display:none;" onchange="uploadFiles(this.files)">
</div>
<?php displayFlash(); ?>

<!-- Upload Drop Zone -->
<div id="drop-zone" class="card" style="padding:50px;text-align:center;border:2px dashed var(--primary);margin-bottom:30px;cursor:pointer;transition:background .2s;" onclick="document.getElementById('file-input').click()">
    <i class="fas fa-cloud-upload-alt" style="font-size:48px;color:var(--primary);margin-bottom:15px;"></i>
    <h3 style="font-weight:700;">Drag & Drop Files Here</h3>
    <p style="color:var(--text-muted);margin-top:5px;">or click to browse — JPG, PNG, GIF, WEBP, MP4, PDF (max 10MB)</p>
    <div id="upload-progress" style="display:none;margin-top:20px;">
        <div style="height:6px;background:var(--bg-body);border-radius:3px;overflow:hidden;">
            <div id="progress-bar" style="height:100%;background:var(--primary);transition:width .3s;width:0%;"></div>
        </div>
        <p id="upload-status" style="margin-top:8px;color:var(--text-muted);font-size:13px;"></p>
    </div>
</div>

<!-- Media Grid -->
<div class="card">
    <div id="media-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:20px;padding:10px;">
        <?php foreach ($media as $file): ?>
        <div class="media-item" style="position:relative;border-radius:12px;overflow:hidden;background:var(--bg-body);cursor:pointer;">
            <?php if (strpos($file['file_type'],'image') !== false): ?>
                <img src="../<?php echo $file['file_path']; ?>" style="width:100%;height:140px;object-fit:cover;" loading="lazy">
            <?php elseif (strpos($file['file_type'],'video') !== false): ?>
                <div style="width:100%;height:140px;display:flex;align-items:center;justify-content:center;background:#000;">
                    <i class="fas fa-play-circle" style="font-size:40px;color:white;"></i>
                </div>
            <?php else: ?>
                <div style="width:100%;height:140px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-file-pdf" style="font-size:40px;color:var(--danger);"></i>
                </div>
            <?php endif; ?>
            <div class="media-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,.6);display:none;flex-direction:column;align-items:center;justify-content:center;gap:8px;">
                <a href="../<?php echo $file['file_path']; ?>" target="_blank" class="btn" style="padding:5px 12px;background:white;color:#000;font-size:12px;"><i class="fas fa-eye"></i> View</a>
                <a href="?delete=<?php echo $file['id']; ?>" onclick="return confirm('Delete this file?')" class="btn" style="padding:5px 12px;background:var(--danger);color:white;font-size:12px;border:none;"><i class="fas fa-trash"></i> Delete</a>
            </div>
            <div style="padding:8px;font-size:11px;font-weight:600;color:var(--text-muted);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;"><?php echo $file['file_name']; ?></div>
        </div>
        <?php endforeach; if (empty($media)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--text-muted);">No files uploaded yet.</div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div style="display:flex;gap:10px;padding:20px;justify-content:center;">
        <?php for ($i=1; $i<=$pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" style="padding:8px 15px;border-radius:8px;font-weight:600;<?php echo ($i == $page) ? 'background:var(--primary);color:white;' : 'background:var(--bg-body);color:var(--text-muted);'; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.media-item:hover .media-overlay { display:flex !important; }
#drop-zone.drag-over { background: rgba(67,97,238,0.07); }
</style>
<script>
const dropZone = document.getElementById('drop-zone');
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('drag-over'); uploadFiles(e.dataTransfer.files); });

function uploadFiles(files) {
    const prog = document.getElementById('upload-progress');
    const bar = document.getElementById('progress-bar');
    const status = document.getElementById('upload-status');
    prog.style.display = 'block';

    Array.from(files).forEach((file, i) => {
        const fd = new FormData();
        fd.append('file', file);
        const xhr = new XMLHttpRequest();
        xhr.upload.onprogress = e => { if (e.lengthComputable) bar.style.width = (e.loaded/e.total*100)+'%'; };
        xhr.onload = () => {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                status.textContent = `Uploaded: ${file.name}`;
                const grid = document.getElementById('media-grid');
                const el = document.createElement('div');
                el.className = 'media-item';
                el.style.cssText = 'position:relative;border-radius:12px;overflow:hidden;background:var(--bg-body);cursor:pointer;';
                el.innerHTML = `<img src="../${res.path}" style="width:100%;height:140px;object-fit:cover;"><div class="media-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,.6);display:none;flex-direction:column;align-items:center;justify-content:center;gap:8px;"><a href="../${res.path}" target="_blank" class="btn" style="padding:5px 12px;background:white;color:#000;font-size:12px;"><i class="fas fa-eye"></i> View</a><a href="?delete=${res.id}" onclick="return confirm('Delete?')" class="btn" style="padding:5px 12px;background:var(--danger);color:white;font-size:12px;border:none;"><i class="fas fa-trash"></i> Delete</a></div><div style="padding:8px;font-size:11px;font-weight:600;color:var(--text-muted);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">${res.name}</div>`;
                grid.prepend(el);
            } else {
                status.textContent = 'Error: ' + res.message;
            }
        };
        xhr.open('POST', 'media.php');
        xhr.send(fd);
    });
}
</script>
<?php include_once 'partials/footer.php'; ?>
