<?php
session_start();
require_once __DIR__ . '/db/config.php';

$guest = null;
$events = [];
$error_message = '';
$success_message = '';

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /#rsvp');
    exit;
}

// Check if a guest is already in session
if (isset($_SESSION['guest_id'])) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM guests WHERE id = ?");
    $stmt->execute([$_SESSION['guest_id']]);
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = db();
    if (isset($_POST['email'])) {
        // Email check
        $stmt = $pdo->prepare("SELECT * FROM guests WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($guest) {
            $_SESSION['guest_id'] = $guest['id'];
        } else {
            $error_message = "Désolé, votre email n'a pas été trouvé. Veuillez vérifier l'adresse ou nous contacter.";
        }
    } elseif (isset($_POST['update_rsvp']) && $guest) {
        // RSVP update
        $person_count = isset($_POST['person_count']) ? intval($_POST['person_count']) : 1;

        foreach ($_POST['events'] as $event_id => $status) {
            $stmt = $pdo->prepare("UPDATE invitations SET attending = ? WHERE guest_id = ? AND event_id = ?");
            $stmt->execute([$status === 'yes' ? 1 : 0, $guest['id'], $event_id]);
        }
        
        // This is a simplified update for person_count, assuming it applies to all 'yes' responses.
        $stmt = $pdo->prepare("UPDATE invitations SET person_count = ? WHERE guest_id = ? AND attending = 1");
        $stmt->execute([$person_count, $guest['id']]);

        $success_message = "Merci ! Votre réponse a été enregistrée.";
        // Refresh guest data to show updated status
        $stmt = $pdo->prepare("SELECT * FROM guests WHERE id = ?");
        $stmt->execute([$guest['id']]);
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// If a guest is identified, fetch their invitations
if ($guest) {
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT e.id, e.name, i.attending, i.person_count
        FROM invitations i
        JOIN events e ON i.event_id = e.id
        WHERE i.guest_id = ?
        ORDER BY e.id
    ");
    $stmt->execute([$guest['id']]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mariage de J & M</title>
    <meta name="description" content="<?php echo htmlspecialchars($_SERVER['PROJECT_DESCRIPTION'] ?? 'Site de mariage pour J & M'); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Lato:wght@400;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css?v=<?php echo time(); ?>">

    <!-- Meta tags for social sharing -->
    <meta property="og:title" content="Mariage de J & M">
    <meta property="og:description" content="<?php echo htmlspecialchars($_SERVER['PROJECT_DESCRIPTION'] ?? 'Nous nous marions ! Rejoignez-nous pour célébrer.'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($_SERVER['PROJECT_IMAGE_URL'] ?? ''); ?>">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta name="twitter:card" content="summary_large_image">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#hero">M&C</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#hero">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#info">Infos Pratiques</a></li>
                    <li class="nav-item"><a class="nav-link" href="programme.php">Programme</a></li>
                    <li class="nav-item"><a class="nav-link" href="#rsvp">RSVP</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header id="hero">
        <img src="assets/pasted-20260104-105018-5b917867.png" alt="Illustration florale" class="illustration">
        <h1>Mélanie & Cyprien</h1>
        <h2>06 Juin 2026</h2>
        <a href="#rsvp" class="btn btn-primary">Répondre</a>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Info Section -->
        <section id="info" class="section">
            <div class="container">
                <h2 class="section-title">Le programme</h2>
                <div class="row text-center">
                    <div class="col-md-4">
                        <h3>Mariage Civil</h3>
                        <p>15h00 - Mairie d'Esternay</p>
                        <p>10 Place du Général de Gaulle, 51310 Esternay</p>
                    </div>
                    <div class="col-md-4">
                        <h3>Cérémonie</h3>
                        <p>16h00 - Domaine Le Saint Léonard</p>
                        <p>6 Rue de l'Église, 77151 Montceaux-lès-Provins</p>
                    </div>
                    <div class="col-md-4">
                        <h3>Coktail</h3>
                        <p>18h00 - Domaine Le Saint Léonard</p>
                        <p>6 Rue de l'Église, 77151 Montceaux-lès-Provins</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Programme Section -->
       

        <!-- RSVP Section -->
        <section id="rsvp" class="section">
            <div class="container">
                <h2 class="section-title">Confirmation de votre présence</h2>
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <?php if (!$guest): ?>
                            <!-- Email Form -->
                            <p class="text-center lead mb-4">Veuillez confirmer votre présence avant le 15 avril 2026.</p>
                            <form action="#rsvp" method="post">
                                <div class="mb-3">
                                    <label for="email" class="form-label visually-hidden">Email</label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Entrez votre adresse email" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Trouver mon invitation</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- RSVP Form -->
                            <div class="text-center">
                                <h3>Bonjour, <?php echo htmlspecialchars($guest['name']); ?> !</h3>
                                <p>Nous sommes ravis de vous inviter aux événements suivants. Merci de nous donner votre réponse pour chacun.</p>
                                <p><a href="?logout=1">Ce n'est pas vous ?</a></p>
                            </div>

                            <form action="#rsvp" method="post">
                                <input type="hidden" name="update_rsvp" value="1">
                                
                                <?php foreach ($events as $event): ?>
                                <div class="mb-3 p-3 border rounded">
                                    <h5 class="mb-3"><?php echo htmlspecialchars($event['name']); ?></h5>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="events[<?php echo $event['id']; ?>]" id="event-yes-<?php echo $event['id']; ?>" value="yes" <?php if ($event['attending'] === 1) echo 'checked'; ?> required>
                                        <label class="form-check-label" for="event-yes-<?php echo $event['id']; ?>">Oui, je serai présent(e)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="events[<?php echo $event['id']; ?>]" id="event-no-<?php echo $event['id']; ?>" value="no" <?php if ($event['attending'] === 0) echo 'checked'; ?> required>
                                        <label class="form-check-label" for="event-no-<?php echo $event['id']; ?>">Non, je ne pourrai pas venir</label>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <div class="mb-3">
                                    <label for="person_count" class="form-label">Nombre de personnes au total (vous inclus)</label>
                                    <input type="number" class="form-control" id="person_count" name="person_count" value="<?php echo htmlspecialchars($events[0]['person_count'] ?? 1); ?>" min="1" max="10">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Valider ma réponse</button>
                                </div>
                            </form>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>Fait avec amour par Mélanie &amp; Cyprien</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>