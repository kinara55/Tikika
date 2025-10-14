<?php
require_once 'conf.php';
require_once 'DB/database.php';
require_once 'session/session_manager.php';

$session = new SessionManager($conf);
$session->requireLogin('forms.html'); // Redirect to login if not logged in
$organizer_id = $session->getUserId();

// Set up navigation variables
$isLoggedIn = $session->isLoggedIn();
$userName = $isLoggedIn ? $session->getUsername() : '';
$userRole = $isLoggedIn ? $session->getRoleId() : 0;
$currentPage = 'create'; // Set current page for active nav highlighting
// Clear old session messages
$session->clearErrors();
$session->getMessage('msg'); // Clear old messages
$session->getMessage('success');
$session->getMessage('error');
// Collect and sanitize form data
$venue = trim($_POST['venue'] ?? '');
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$start_datetime = $_POST['start_datetime'] ?? null;
$end_datetime = $_POST['end_datetime'] ?? null;
$status = $_POST['status'] ?? 'draft';
$capacity = $_POST['capacity'] ?? null;
$category_id = $_POST['category_id'] ?? null;

// Basic validation
$errors = [];
if (!$title) $errors[] = 'Event title is required.';
if (!$start_datetime) $errors[] = 'Start date/time is required.';
if (!$status) $errors[] = 'Event status is required.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($errors)) {
        $session->setErrors($errors);
        // Optionally, you can redirect back to the form to show errors
        // header('Location: create_event.php'); exit;
    }
}

$db = new Database($conf);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    // Handle image upload
    $image_url = null;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/events/';
        $file_extension = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $filename = 'event_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                $image_url = $upload_path;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        } else {
            $errors[] = 'Invalid image format. Please upload JPG, PNG, GIF, or WebP files.';
        }
    }
    
    if (empty($errors)) {
        $sql = "INSERT INTO events (organizer_id, title, description, venue, start_datetime, end_datetime, status, capacity, category_id, image_url)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $db->query($sql, [
                $organizer_id,
                $title,
                $description,
                $venue ?: null,
                $start_datetime,
                $end_datetime ?: null,
                $status,
                $capacity ?: null,
                $category_id ?: null,
                $image_url
            ]);
            $session->setMessage('success', 'Event created successfully!');
           
            header('Location: create_event.php'); exit;
        } catch (Exception $e) {
            $session->setMessage('error', 'Database error: '.htmlspecialchars($e->getMessage()));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Multi-Step Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="event-body">
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>
    
    <div class="event-container">
        <div class="eventform-header">
            <h1>Create New Event</h1>
            <p>Fill in the details to create your event</p>
        </div>
        <div class="eventform-container">
            <?php
            // Show error messages if any
            $errors = $session->getErrors();
            if (!empty($errors)) {
                echo '<div class="error-message">'.implode('<br>', $errors).'</div>';
            }
            // Show success/error messages
            $successMsg = $session->getMessage('success');
            if ($successMsg) {
                echo '<div class="success-message">'.htmlspecialchars($successMsg).'</div>';
            }
            $errorMsg = $session->getMessage('error');
            if ($errorMsg) {
                echo '<div class="error-message">'.htmlspecialchars($errorMsg).'</div>';
            }
            // Fetch user info for organizer fields
            $user = null;
            if ($organizer_id) {
                $user = $db->fetchOne("SELECT full_name, email, phone FROM users WHERE id = ?", [$organizer_id]);
            }
            ?>
            <form id="event-form" action="create_event.php" method="POST" enctype="multipart/form-data">
                <!-- Step 1: Organizer Info -->
                <div class="form-step" id="step-1">
                    <h2>Organizer Info</h2>
                    <input type="hidden" name="organizer_id" value="<?php echo htmlspecialchars($organizer_id); ?>">
                    <div class="form-group">
                        <label for="organizer_name">Organizer Name</label>
                        <input type="text" id="organizer_name" name="organizer_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="organizer_email">Organizer Email</label>
                        <input type="email" id="organizer_email" name="organizer_email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="organizer_phone">Organizer Phone</label>
                        <input type="tel" id="organizer_phone" name="organizer_phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly>
                    </div>
                    <button type="button" class="btn-primary" id="next-1">Next</button>
                </div>
                <!-- Step 2: Event Details -->
                <div class="form-step" id="step-2" style="display:none;">
                    <h2>Event Details</h2>
                    <div class="form-group">
                        <label for="title">Event Title <span class="required">*</span></label>
                        <input type="text" id="title" name="title" maxlength="200" required placeholder="Enter an engaging event title">
                    </div>
                    <div class="form-group">
                        <label for="description">Event Description</label>
                        <textarea id="description" name="description" placeholder="Describe your event in detail. What can attendees expect?"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="venue_id">Venue</label>
                        <select id="venue_id" name="venue_id">
                            <option value="">Select Venue (Optional)</option>
                            <option value="1">Nairobi Concert Hall - 123 Main Street, Nairobi (Capacity: 5000)</option>
                            <option value="2">Uhuru Gardens - Uhuru Highway, Nairobi (Capacity: 10000)</option>
                            <option value="3">Beer District - Westlands, Nairobi (Capacity: 2000)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="capacity">Event Capacity</label>
                        <input type="number" id="capacity" name="capacity" min="1" max="100000" placeholder="Maximum number of attendees">
                    </div>
                    <div class="form-group">
                        <label for="event_image">Event Image</label>
                        <input type="file" id="event_image" name="event_image" accept="image/*">
                        <small class="form-text text-muted">Upload JPG, PNG, GIF, or WebP image (max 5MB)</small>
                    </div>
                    <button type="button" class="btn-primary" id="prev-2">Previous</button>
                    <button type="button" class="btn-primary" id="next-2">Next</button>
                </div>
                <!-- Step 3: Schedule & Categories -->
                <div class="form-step" id="step-3" style="display:none;">
                    <h2>Schedule & Categories</h2>
                    <div class="form-group">
                        <label for="start_datetime">Start Date & Time <span class="required">*</span></label>
                        <input type="datetime-local" id="start_datetime" name="start_datetime" required>
                    </div>
                    <div class="form-group">
                        <label for="end_datetime">End Date & Time</label>
                        <input type="datetime-local" id="end_datetime" name="end_datetime">
                    </div>
                    <div class="form-group">
                        <label for="category_id">Event Category <span class="required">*</span></label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <option value="1">Music</option>
                            <option value="2">Conference</option>
                            <option value="3">Meetup</option>
                            <option value="4">Sports</option>
                            <option value="5">Workshop</option>
                            <option value="6">Exhibition</option>
                            <option value="7">Festival</option>
                            <option value="8">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Event Status <span class="required">*</span></label>
                        <select id="status" name="status" required>
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <button type="button" class="btn-primary" id="prev-3">Previous</button>
                    <button type="submit" class="btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Multi-step navigation
        document.getElementById('next-1').onclick = function() {
            document.getElementById('step-1').style.display = 'none';
            document.getElementById('step-2').style.display = 'block';
        };
        document.getElementById('prev-2').onclick = function() {
            document.getElementById('step-2').style.display = 'none';
            document.getElementById('step-1').style.display = 'block';
        };
        document.getElementById('next-2').onclick = function() {
            document.getElementById('step-2').style.display = 'none';
            document.getElementById('step-3').style.display = 'block';
        };
        document.getElementById('prev-3').onclick = function() {
            document.getElementById('step-3').style.display = 'none';
            document.getElementById('step-2').style.display = 'block';
        };
    </script>
</body>
</html>