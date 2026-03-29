<?php
// config/mailer.php
// Konfigurasi SMTP untuk pengiriman email
// Gunakan akun Gmail dengan App Password (bukan password utama)
// Panduan: https://myaccount.google.com/apppasswords

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// ============================================================
//  ISI SESUAI AKUN BREVO (https://app.brevo.com)
//  SMTP Key ada di: Settings > SMTP & API > SMTP
// ============================================================
define('MAIL_HOST',     'smtp-relay.brevo.com');
define('MAIL_PORT',     587);
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');  // Email login Brevo Anda
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');  // SMTP Key dari Brevo
define('MAIL_FROM',     $_ENV['MAIL_FROM']     ?? '');
define('MAIL_FROM_NAME','Sarpras An Nadzir');
// ============================================================

/**
 * Kirim email menggunakan PHPMailer + Gmail SMTP
 * 
 * @param string|array $to       Email tujuan (string atau array ['email@x.com','Nama'])
 * @param string       $subject  Judul email
 * @param string       $body     Body HTML email
 * @return bool
 */
function kirimEmail($to, string $subject, string $body): bool
{
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        // Pengirim
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);

        // Penerima
        if (is_array($to)) {
            foreach ($to as $email) {
                if (!empty($email)) $mail->addAddress(trim($email));
            }
        } else {
            if (!empty($to)) $mail->addAddress(trim($to));
        }

        // Konten
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
