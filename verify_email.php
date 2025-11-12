<?php
session_start();
require_once 'conf.php';
require_once 'DB/database.php';
require_once 'session/session_manager.php';

$db = new Database($conf);
$sessionManager = new SessionManager($conf);

// Check if user is in pending verification
if (!isset($_SESSION['pending_verification'])) {
    header('Location: forms.html');
    exit;
}

$pendingUser = $_SESSION['pending_verification'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredCode = trim($_POST['code'] ?? '');
    
    if (empty($enteredCode)) {
        $errorMessage = 'Please enter the verification code.';
    } else {
        // Validate the verification code
        $verification = $db->fetchOne(
            "SELECT id, code, expires_at, used_at FROM verification_codes 
             WHERE user_id = ? AND code = ? AND used_at IS NULL 
             ORDER BY id DESC LIMIT 1",
            [$pendingUser['user_id'], $enteredCode]
        );
        
        if (!$verification) {
            $errorMessage = 'Invalid verification code.';
        } elseif (new DateTime() > new DateTime($verification['expires_at'])) {
            $errorMessage = 'Verification code has expired. Please request a new one.';
        } else {
            // Code is valid - verify the user
            $db->beginTransaction();
            try {
                // Update user as verified
                $db->update('users', ['is_verified' => 1], 'id = ?', [$pendingUser['user_id']]);
                
                // Mark verification code as used
                $db->update('verification_codes', 
                    ['used_at' => (new DateTime())->format('Y-m-d H:i:s')], 
                    'id = ?', 
                    [$verification['id']]
                );
                
                $db->commit();
                
                // Clear pending verification session
                unset($_SESSION['pending_verification']);
                
                // Set success message and redirect to login
                $sessionManager->setMessage('msg', 'Email verified successfully! You can now log in to your account.');
                header('Location: forms.html');
                exit;
                
            } catch (Exception $e) {
                $db->rollback();
                $errorMessage = 'Verification failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Tikika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .verification-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .verification-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }
        
        .verification-header {
            background: linear-gradient(45deg, #ff6b81, #ff914d);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .verification-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .verification-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        .verification-body {
            padding: 2rem;
        }
        
        .email-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .email-info i {
            color: #ff6b81;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .code-input {
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 0.5rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            transition: all 0.3s ease;
        }
        
        .code-input:focus {
            border-color: #ff6b81;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 129, 0.25);
            outline: none;
        }
        
        .btn-verify {
            background: linear-gradient(45deg, #ff6b81, #ff914d);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 129, 0.4);
            color: white;
        }
        
        .btn-resend {
            background: none;
            border: 2px solid #ff6b81;
            color: #ff6b81;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-resend:hover {
            background: #ff6b81;
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 1rem;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .instructions {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0 10px 10px 0;
        }
        
        .instructions h6 {
            color: #1976d2;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .instructions ul {
            margin: 0;
            padding-left: 1.2rem;
            color: #1976d2;
        }
        
        @media (max-width: 576px) {
            .verification-card {
                margin: 10px;
            }
            
            .verification-header {
                padding: 1.5rem;
            }
            
            .verification-body {
                padding: 1.5rem;
            }
            
            .code-input {
                font-size: 1.2rem;
                letter-spacing: 0.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <div class="verification-header">
                <h1><i class="fas fa-envelope-open"></i> Verify Email</h1>
                <p>Check your email for the verification code</p>
            </div>
            
            <div class="verification-body">
                <div class="email-info">
                    <i class="fas fa-paper-plane"></i>
                    <p class="mb-0">We've sent a verification code to:</p>
                    <strong><?php echo htmlspecialchars($pendingUser['email']); ?></strong>
                </div>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>
                
                <div class="instructions">
                    <h6><i class="fas fa-info-circle me-2"></i>Instructions:</h6>
                    <ul>
                        <li>Check your email inbox (and spam folder)</li>
                        <li>Enter the 6-digit code below</li>
                        <li>The code expires in 15 minutes</li>
                    </ul>
                </div>
                
                <form method="POST">
                    <div class="mb-3">
                        <input type="text" 
                               class="form-control code-input" 
                               name="code" 
                               placeholder="000000" 
                               maxlength="6" 
                               pattern="[0-9]{6}"
                               required
                               autocomplete="off">
                    </div>
                    
                    <button type="submit" class="btn-verify">
                        <i class="fas fa-check me-2"></i>Verify Email
                    </button>
                </form>
                
                <button type="button" class="btn-resend" onclick="resendCode()">
                    <i class="fas fa-redo me-2"></i>Resend Code
                </button>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        Didn't receive the email? 
                        <a href="forms.html" style="color: #ff6b81;">Back to Login</a>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus on code input
        document.querySelector('.code-input').focus();
        
        // Format input to only allow numbers
        document.querySelector('.code-input').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        function resendCode() {
            if (confirm('Resend verification code to <?php echo htmlspecialchars($pendingUser['email']); ?>?')) {
                // You can implement resend functionality here
                alert('Verification code resent! Please check your email.');
            }
        }
    </script>
</body>
</html>




