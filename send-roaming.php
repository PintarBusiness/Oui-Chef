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

$to      = 'ouichef.co@gmail.com';
$subject = '=?UTF-8?B?' . base64_encode('Roaming povpraševanje — ' . $ime) . '?=';

$body  = "Novo roaming povpraševanje s spletne strani:\n\n";
$body .= "Ime: $ime\n";
$body .= "E-pošta: $email\n";
$body .= "Telefon: " . ($tel ?: '—') . "\n";
$body .= "Datum dogodka: " . ($datum ?: '—') . "\n";
$body .= "Lokacija: " . ($lok ?: '—') . "\n";
$body .= "Število gostov: " . ($gosti ?: '—') . "\n\n";
$body .= "Naročilo:\n" . ($narocilo ?: '—') . "\n";

$headers  = "From: =?UTF-8?B?" . base64_encode('Oui Chef Spletna Stran') . "?= <info@ouichef.si>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: base64\r\n";

$sent = mail($to, $subject, base64_encode($body), $headers, '-f info@ouichef.si');

echo json_encode(['ok' => $sent, 'msg' => $sent ? 'Poslano!' : 'Napaka pri pošiljanju.']);