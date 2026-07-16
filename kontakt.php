<?php
/**
 * JoLoTex GmbH – Kontaktformular
 * Empfänger: info@jolotex.de
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Nur POST erlaubt
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Eingaben bereinigen
$name      = trim(strip_tags($_POST['name']      ?? ''));
$email     = trim(strip_tags($_POST['email']     ?? ''));
$telefon   = trim(strip_tags($_POST['telefon']   ?? ''));
$leistung  = trim(strip_tags($_POST['leistung']  ?? ''));
$nachricht = trim(strip_tags($_POST['nachricht'] ?? ''));

// Validierung
if (mb_strlen($name) < 2) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Name fehlt']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'E-Mail ungültig']);
    exit;
}
if (mb_strlen($nachricht) < 5) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nachricht zu kurz']);
    exit;
}

// Empfänger
$to      = 'info@jolotex.de';
$subject = '=?UTF-8?B?' . base64_encode('Neue Anfrage von ' . $name . ' – JoLoTex GmbH') . '?=';

// E-Mail-Text
$body  = "Neue Kontaktanfrage über die Website JoLoTex GmbH\n";
$body .= str_repeat('-', 50) . "\n\n";
$body .= "Name:      " . $name             . "\n";
$body .= "E-Mail:    " . $email            . "\n";
$body .= "Telefon:   " . ($telefon ?: '-') . "\n";
$body .= "Leistung:  " . ($leistung ?: '-') . "\n\n";
$body .= "Nachricht:\n" . $nachricht . "\n\n";
$body .= str_repeat('-', 50) . "\n";
$body .= "Gesendet am: " . date('d.m.Y \u\m H:i \U\h\r') . "\n";

// Header
$headers  = "From: noreply@jolotex.de\r\n";
$headers .= "Reply-To: " . $name . " <" . $email . ">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Senden
$sent = mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'E-Mail konnte nicht gesendet werden']);
}
