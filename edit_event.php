<?php
session_start();

if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 2) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/conf.php';
require_once __DIR__ . '/DB/database.php';

$db_conf = [
    'DB_HOST' => $conf['DB_HOST'],
    'DB_USER' => $conf['DB_USER'],
    'DB_PASS' => $conf['DB_PASS'],
    'DB_NAME' => $conf['DB_NAME']
];

$db = new Database($db_conf);
$organizer_id = $_SESSION['user_id'];
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$event = $db->fetchOne("SELECT * FROM events WHERE id = ? AND organizer_id = ?", [$event_id, $organizer_id]);

if (!$event) {
    header("Location: organizer_dashboard.php");
    exit();
}

$categories = $db->fetchAll("SELECT id, name FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $start_datetime = $_POST['start_datetime'] ?? null;
    $end_datetime = $_POST['end_datetime'] ?? null;
    $status = $_POST['status'] ?? 'draft';
    $capacity = $_POST['capacity'] ?? null;
    $category_id = $_POST['category_id'] ?? null;
    
    $errors = [];
    if (!$title) $errors[] = 'Event title is required.';
    if (!$start_datetime) $errors[] = 'Start date/time is required.';
    
    if (empty($errors)) {
        $updateData = [
            'title' => $title,
            'description' => $description ?: null,
            'venue' => $venue ?: null,
            'start_datetime' => $start_datetime,
            'end_datetime' => $end_datetime ?: null,
            'status' => $status,
            'capacity' => $capacity ? ($capacity > 0 ? $capacity : null) : null,
            'category_id' => $category_id ? (int)$category_id : null
        ];
        
        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'images/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_extension = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($file_extension, $allowed_extensions)) {
                $filename = 'event_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                    $updateData['image_url'] = $upload_path;
                }
            }
        }
        
        try {
            $db->update('events', $updateData, 'id = ?', [$event_id]);
            header('Location: organizer_dashboard.php?success=1');
            exit();
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Event - Tikika</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: "Poppins", sans-serif;
    }
    .navbar {
      background: linear-gradient(90deg, #ff0066, #ff6600);
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg px-4">
  <a class="navbar-brand text-white" href="organizer_dashboard.php">← Back to Dashboard</a>
</nav>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow">
        <div class="card-header bg-danger text-white">
          <h3 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Event</h3>
        </div>
        <div class="card-body">
          <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          
          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="title" class="form-label">Event Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>
            </div>
            
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
            </div>
            
            <div class="mb-3">
              <label for="venue" class="form-label">Venue</label>
              <input type="text" class="form-control" id="venue" name="venue" value="<?= htmlspecialchars($event['venue'] ?? '') ?>">
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="start_datetime" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" 
                       value="<?= date('Y-m-d\TH:i', strtotime($event['start_datetime'])) ?>" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="end_datetime" class="form-label">End Date & Time</label>
                <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" 
                       value="<?= $event['end_datetime'] ? date('Y-m-d\TH:i', strtotime($event['end_datetime'])) : '' ?>">
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                  <option value="">Select Category</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $event['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cat['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="draft" <?= $event['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                  <option value="published" <?= $event['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                  <option value="cancelled" <?= $event['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="capacity" class="form-label">Capacity</label>
              <input type="number" class="form-control" id="capacity" name="capacity" 
                     value="<?= $event['capacity'] ?? '' ?>" min="1">
            </div>
            
            <div class="mb-3">
              <label for="event_image" class="form-label">Event Image</label>
              <?php if ($event['image_url']): ?>
                <div class="mb-2">
                  <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="Current image" style="max-width: 200px; border-radius: 8px;">
                </div>
              <?php endif; ?>
              <input type="file" class="form-control" id="event_image" name="event_image" accept="image/*">
              <small class="form-text text-muted">Leave empty to keep current image</small>
            </div>
            
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-danger btn-lg">
                <i class="fas fa-save me-2"></i>Save Changes
              </button>
              <a href="organizer_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

