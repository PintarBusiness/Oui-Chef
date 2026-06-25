<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Method not allowed']);
    exit;
}

$ime   = trim($_POST['ime'] ?? '');
$email = trim($_POST['email'] ?? '');
$vrsta = trim($_POST['vrsta'] ?? '');
$msg   = trim($_POST['sporocilo'] ?? '');

if (!$ime || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Manjkajoči podatki.']);
    exit;
}

// ── PHPMailer (Composer autoload ali Neoserv pot) ──────────────────
// Na Neoservu: naložiš PHPMailer v /vendor/ ali uporabiš require spodaj
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Poskusi Composer autoload, sicer ročna pot
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    require __DIR__ . '/PHPMailer/src/Exception.php';
    require __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer/src/SMTP.php';
}

// ── Gmail SMTP nastavitve ──────────────────────────────────────────
define('GMAIL_USER', 'ouichef.co@gmail.com');
define('GMAIL_PASS', 'TVOJE_APP_GESLO_TUKAJ');  // ← Google App Password (16 znakov)
define('PREJEMNIK',  'ouichef.co@gmail.com');

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = GMAIL_USER;
    $mail->Password   = GMAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(GMAIL_USER, 'Oui Chef Spletna Stran');
    $mail->addAddress(PREJEMNIK, 'Oui Chef');
    $mail->addReplyTo($email, $ime);

    $mail->Subject = 'Povpraševanje — ' . $ime;

    $mail->Body  = "Novo povpraševanje s spletne strani:\n\n";
    $mail->Body .= "Ime: $ime\n";
    $mail->Body .= "E-pošta: $email\n";
    $mail->Body .= "Vrsta povpraševanja: " . ($vrsta ?: '—') . "\n\n";
    $mail->Body .= "Sporočilo:\n" . ($msg ?: '—') . "\n";

    $mail->send();
    echo json_encode(['ok' => true, 'msg' => 'Poslano!']);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'Napaka: ' . $mail->ErrorInfo]);
}