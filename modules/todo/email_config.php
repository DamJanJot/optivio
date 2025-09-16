<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

function sendReminder($email, $subject, $body) {
    // Sprawdź czy już wysłano (plik cache na serwerze)
    $hash = md5($email . $subject);
    $cacheFile = __DIR__ . "/email_sent_cache/" . $hash . ".txt";

    // Jeśli plik istnieje i ma dzisiejszą datę, nie wysyłaj ponownie
    if (file_exists($cacheFile) && file_get_contents($cacheFile) == date("Y-m-d")) {
        return;
    }

    // Jeśli nie istnieje - wysyłamy
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'mail.cba.pl';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kontakt@projekty-d.j.pl';
        $mail->Password   = 'Nowehaslo777';  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('kontakt@projekty-d.j.pl', 'TODO');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();

        // Zapisz do cache że dzisiaj już wysłano
        if (!file_exists(__DIR__ . "/email_sent_cache")) {
            mkdir(__DIR__ . "/email_sent_cache", 0777, true);
        }
        file_put_contents($cacheFile, date("Y-m-d"));
    } catch (Exception $e) {
        // Error handling - można logować do pliku
    }
}
?>
