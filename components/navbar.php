<?php

// Default values if not set
$isLoggedIn = $isLoggedIn ?? false;
$userName = $userName ?? '';
$userRole = $userRole ?? 0;
$currentPage = $currentPage ?? 'home';
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Tikika</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $currentPage === 'events' ? 'active' : ''; ?>" href="events.php">Events</a>
        </li>
        <?php if ($isLoggedIn && $userRole == 2): ?>
        <li class="nav-item">
          <a class="nav-link <?php echo $currentPage === 'create' ? 'active' : ''; ?>" href="create_event.php">Create Event</a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>" href="about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>" href="contact.php">Contact</a>
        </li>
      </ul>
      
      <!-- User section on the far right -->
      <ul class="navbar-nav">
        <?php if ($isLoggedIn): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($userName); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="cart.php"><i class="fas fa-shopping-cart me-2"></i>My Cart</a></li>
              <?php if ($userRole == 1 ): ?>
              <li><a class="dropdown-item" href="admin_dashboard.php"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="forms.html">
              <i class="fas fa-sign-in-alt me-1"></i>Log in
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>