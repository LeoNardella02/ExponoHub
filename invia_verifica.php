
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer-master/src/Exception.php';
require 'PHPMailer/PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer/PHPMailer-master/src/SMTP.php';

function inviaEmailVerifica($email, $token) {
    $mail = new PHPMailer(true);

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ]
    ];

    try {
        // Impostazioni server SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 
        $mail->Password   = 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Mittente e destinatario
        $mail->setFrom('tersigni.2076642@studenti.uniroma1.it', 'ExpoonHub');
        $mail->addAddress($email);

        // Contenuto dell'email
        $mail->isHTML(true);
        $mail->Subject = 'Conferma la tua email - ExpoonHub';
        $link = "http://localhost/ExponoHub/verifica_email.php?email=" . urlencode($email) . "&token=" . urlencode($token);
        $mail->Body    = "Clicca sul seguente link per verificare il tuo account:<br><a href='$link'>$link</a>";

        $mail->send();

    } catch (Exception $e) {
       
    }
}
?>
