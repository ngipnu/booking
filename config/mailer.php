<?php
// config/mailer.php
// Konfigurasi SMTP untuk pengiriman email (Brevo Relay)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Pastikan file .env terbaca jika belum ada di $_ENV
if (empty($_ENV['MAIL_USERNAME'])) {
    $_env_file = dirname(__DIR__) . '/.env';
    if (file_exists($_env_file)) {
        foreach (file($_env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
            $_line = trim($_line);
            if (empty($_line) || strpos($_line, '#') === 0) continue;
            if (strpos($_line, '=') !== false) {
                [$_k, $_v] = explode('=', $_line, 2);
                $_k = trim($_k);
                $_v = trim($_v);
                $_ENV[$_k] = $_v;
                putenv("$_k=$_v");
            }
        }
    }
}

if (!defined('MAIL_HOST'))      define('MAIL_HOST',      $_ENV['MAIL_HOST'] ?? 'smtp-relay.brevo.com');
if (!defined('MAIL_PORT'))      define('MAIL_PORT',      (int)($_ENV['MAIL_PORT'] ?? 587));
if (!defined('MAIL_USERNAME'))  define('MAIL_USERNAME',  $_ENV['MAIL_USERNAME'] ?? '');
if (!defined('MAIL_PASSWORD'))  define('MAIL_PASSWORD',  $_ENV['MAIL_PASSWORD'] ?? '');
if (!defined('MAIL_FROM'))      define('MAIL_FROM',      $_ENV['MAIL_FROM'] ?? '');
if (!defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Sarpras An Nadzir');

/**
 * Kirim email menggunakan PHPMailer + Brevo SMTP
 * 
 * @param string|array $to       Email tujuan (string atau array string email)
 * @param string       $subject  Judul email
 * @param string       $body     Body HTML email
 * @param string|null  &$errorMsg Output detail error jika pengiriman gagal
 * @return bool
 */
function kirimEmail($to, string $subject, string $body, ?string &$errorMsg = null): bool
{
    $mail = new PHPMailer(true);
    try {
        if (empty(MAIL_USERNAME) || empty(MAIL_PASSWORD) || empty(MAIL_FROM)) {
            $errorMsg = "Kredensial email di .env belum lengkap (MAIL_USERNAME / MAIL_PASSWORD / MAIL_FROM kosong).";
            error_log("PHPMailer Error: " . $errorMsg);
            return false;
        }

        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->AuthType   = 'LOGIN';   // Brevo wajib LOGIN, bukan CRAM-MD5
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 15;

        // Pengirim
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);

        // Penerima
        $has_recipient = false;
        if (is_array($to)) {
            foreach ($to as $email) {
                $email = trim((string)$email);
                if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->addAddress($email);
                    $has_recipient = true;
                }
            }
        } else {
            $to = trim((string)$to);
            if (!empty($to) && filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($to);
                $has_recipient = true;
            }
        }

        if (!$has_recipient) {
            $errorMsg = "Tidak ada alamat email penerima yang valid.";
            error_log("PHPMailer Error: " . $errorMsg);
            return false;
        }

        // Konten
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

        $mail->send();
        return true;
    } catch (Exception $e) {
        $errorMsg = $mail->ErrorInfo ?: $e->getMessage();
        error_log("PHPMailer Error: " . $errorMsg);
        return false;
    }
}
?>
