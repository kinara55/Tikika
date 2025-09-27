<?php
// Define events (replace later with DB fetch)
$events = [
    1 => ["title" => "AfroBeats Night", "date" => "Oct 15, 2025", "price" => "Ksh 1500", "image" => "images/image1.jpg"],
    2 => ["title" => "Jazz Festival", "date" => "Nov 2, 2025", "price" => "Ksh 2000", "image" => "images/image2.jpg"],
    3 => ["title" => "Bambika na 3 men Army", "date" => "Dec 10, 2025", "price" => "Ksh 2500", "image" => "images/image3.jpg"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tikika - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <style>
   
    body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; color: #333; overflow-x: hidden; }
    .navbar { background: linear-gradient(90deg, #ff0066, #ff6600); }
    .navbar-brand, .nav-link { font-weight: 600; }
    .nav-link:hover { color: #ffe600 !important; }
    .hero-section {
      background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7)), url('img/hero-bg.jpg') center/cover no-repeat;
      height: 50vh; color: #fff; text-shadow: 2px 2px 10px rgba(0,0,0,0.6);
      animation: fadeIn 2s ease-in-out;
    }
    .hero-section h1 { font-size: 3.5rem; color: #ffe600; animation: slideDown 1.5s ease forwards; }
    .hero-section p { font-size: 1.25rem; opacity: 0; animation: fadeUp 2s ease forwards; animation-delay: 1s; }
    .hero-section .btn { background: #ff0066; border: none; padding: 12px 30px; font-size: 1.2rem; border-radius: 30px; box-shadow: 0 0 10px #ff0066; transition: all 0.3s ease; }
    .hero-section .btn:hover { background: #ff6600; box-shadow: 0 0 20px #ff6600; transform: scale(1.05); }

    h2 { font-weight: 700; color: #ff0066; position: relative; display: inline-block; animation: fadeIn 2s ease-in-out; }
    .card { border: none; opacity: 0; transform: translateY(50px); transition: transform 0.5s ease, box-shadow 0.3s ease, opacity 0.5s ease; }
    .card.show { opacity: 1; transform: translateY(0); }
    .card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 8px 25px rgba(255,0,102,0.3); }
    .card-title { color: #ff6600; font-weight: 600; }
    .btn-outline-secondary { border-color: #ff0066; color: #ff0066; border-radius: 30px; }
    .btn-outline-secondary:hover { background: #ff0066; color: #fff; box-shadow: 0 0 15px #ff0066; }

    .social-links a { font-size: 1.8rem; color: #ff0066; transition: color 0.3s, transform 0.3s; }
    .social-links a:hover { color: #ff6600; transform: scale(1.2); }

    .card-img-top { width: 180px; height: 180px; object-fit: cover; border-radius: 50%; margin: 20px auto 10px; display: block; box-shadow: 0 4px 15px rgba(255, 0, 102, 0.3); transition: transform 0.3s ease; }
    .card-img-top:hover { transform: scale(1.08); }

    footer { background: linear-gradient(90deg, #333); color: #fff; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes fadeUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">Tikika</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="forms.html">Log in</a></li>
          <li class="nav-item"><a class="nav-link" href="events.html">Events</a></li>
          <li class="nav-item"><a class="nav-link" href="create_event.php">Create Event</a></li>
          <li class="nav-item"><a class="nav-link" href="about.html">About</a></li>
          <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero-section d-flex flex-column align-items-center justify-content-center text-center">
    <div>
      <h1 class="fw-bold">Welcome to Tikika</h1>
      <p class="lead">Discover and book tickets for the hottest concerts & events!</p>
      <a href="events.html" class="btn btn-lg mt-3">🎟️ Browse Events</a>
    </div>
  </section>

  <!-- Featured Events -->
  <div class="container my-5">
    <h2 class="text-center mb-4">🔥 Upcoming Events</h2>
    <div class="row">
      <?php foreach($events as $id => $event): ?>
      <div class="col-md-4 mb-4">
        <div class="card shadow-lg h-100">
          <img src="<?php echo $event['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($event['title']); ?>">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
            <p class="card-text">📅 <?php echo $event['date']; ?><br>💸 <?php echo $event['price']; ?></p>
            <div class="mt-auto">
              <a href="event_details.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary">View Details</a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Footer -->
  <footer class="text-center py-4">
    <p>&copy; 2025 Tikika. All Rights Reserved.</p>
    <p>
      <a href="about.html" class="me-3">About</a>
      <a href="contact.html">Contact</a>
    </p>
    <div class="social-links mt-3">
      <a href="https://www.instagram.com/tikika_events" target="_blank" class="me-3">
        <i class="bi bi-instagram"></i>
      </a>
      <a href="https://www.tiktok.com/@yourpage" target="_blank">
        <i class="bi bi-tiktok"></i>
      </a>
    </div>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    
    const cards = document.querySelectorAll('.card');
    function revealCards() {
      const triggerBottom = window.innerHeight * 0.85;
      cards.forEach(card => {
        const cardTop = card.getBoundingClientRect().top;
        if (cardTop < triggerBottom) card.classList.add('show');
      });
    }
    window.addEventListener('load', revealCards);
    window.addEventListener('scroll', revealCards);
  </script>

</body>
</html>
