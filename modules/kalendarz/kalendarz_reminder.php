<?php require_once __DIR__ . '/../core/env_loader.php'; 
require_once 'connect.php';
require_once 'env_loader.php';

// dodaj te 3 linie:
require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';
require_once 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$date = date('Y-m-d');



// Pobierz wydarzenia na dziś z e-mailem użytkownika
$stmt = $pdo->prepare("
  SELECT w.tytul, w.opis, w.data, w.godzina, u.email, u.imie
  FROM wydarzenia w
  JOIN uzytkownicy u ON w.user_id = u.id
  WHERE w.data = ?
");
$stmt->execute([$date]);
$wydarzenia = $stmt->fetchAll();

foreach ($wydarzenia as $w) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom('kontakt@projekty-d.j.pl', 'KALENDARZ');
        $mail->addAddress($w['email'], $w['imie']);
        $mail->isHTML(true);
        $mail->Subject = 'Kalendarz - Przypomnienie o wydarzeniu: ' . $w['tytul'];
        $mail->Body = '
          <p>Hej ' . htmlspecialchars($w['imie']) . '!</p>
          <p>Masz dziś zaplanowane wydarzenie:</p>
          <ul>
            <li><strong>Tytuł:</strong> ' . htmlspecialchars($w['tytul']) . '</li>
            <li><strong>Godzina:</strong> ' . htmlspecialchars($w['godzina']) . '</li>
            <li><strong>Opis:</strong> ' . nl2br(htmlspecialchars($w['opis'])) . '</li>
          </ul>
          <br>
          <p>Miłego dnia!<br>Zespół MyApp</p>
        ';
        $mail->send();
    } catch (Exception $e) {
        error_log("Błąd maila: " . $e->getMessage());
    }
}
?>