<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Method not allowed']);
    exit;
}

$ime      = trim($_POST['ime'] ?? '');
$email    = trim($_POST['email'] ?? '');
$tel      = trim($_POST['tel'] ?? '');
$datum    = trim($_POST['datum'] ?? '');
$lok      = trim($_POST['lokacija'] ?? '');
$gosti    = trim($_POST['gosti'] ?? '');
$narocilo = trim($_POST['narocilo'] ?? '');

if (!$ime || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'Manjkajoči podatki.']);
    exit;
}

// ── PHPMailer ─────────────────────────────────────────────────────
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    $mail->Subject = 'Roaming povpraševanje — ' . $ime;

    $mail->Body  = "Novo roaming povpraševanje s spletne strani:\n\n";
    $mail->Body .= "Ime: $ime\n";
    $mail->Body .= "E-pošta: $email\n";
    $mail->Body .= "Telefon: " . ($tel ?: '—') . "\n";
    $mail->Body .= "Datum dogodka: " . ($datum ?: '—') . "\n";
    $mail->Body .= "Lokacija: " . ($lok ?: '—') . "\n";
    $mail->Body .= "Število gostov: " . ($gosti ?: '—') . "\n\n";
    $mail->Body .= "Naročilo:\n" . ($narocilo ?: '—') . "\n";

    $mail->send();
    echo json_encode(['ok' => true, 'msg' => 'Poslano!']);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'Napaka: ' . $mail->ErrorInfo]);
}