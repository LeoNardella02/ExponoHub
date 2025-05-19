
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

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
        $mail->Username   = 'exponohub@gmail.com';   
        $mail->Password   = '';  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Mittente e destinatario
        $mail->setFrom('exponohub@gmail.com', 'ExponoHub');
        $mail->addAddress($email);

        // Contenuto dell'email
        $mail->isHTML(true);
        $mail->Subject = 'Conferma la tua email - ExponoHub';
        $link = "http://localhost:3000/verifica_email.php?email=" . urlencode($email) . "&token=" . urlencode($token);
        $mail->Body    = "Clicca sul seguente link per verificare il tuo account:<br><a href='$link'>$link</a>";

        $mail->send();

    } catch (Exception $e) {
       
    }
}
?>
