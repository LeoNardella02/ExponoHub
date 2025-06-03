<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function inviaEmailVerifica($email, $token) {
    $mail = new PHPMailer(true);

    // Configurazione SSL per sviluppo locale
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
        $mail->Password   = '****';         
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Mittente e destinatario
        $mail->setFrom('exponohub@gmail.com', 'ExponoHub');
        $mail->addAddress($email);

        // Contenuto dell'email
        $mail->isHTML(true);
        $mail->Subject = 'Conferma la tua email - ExponoHub';
        $link = "http://localhost/ExponoHub/verifica_email.php?email=" . urlencode($email) . "&token=" . urlencode($token);
        $mail->Body = "
        <div style='background-color: #9dbcdb; font-family: Arial, sans-serif; text-align: center; padding: 20px;'>
            <h2 style='font-size: 24px; margin-bottom: 20px;'>ExponoHub</h2>
            
            <p style='font-size: 16px; margin-bottom: 20px;'>
                Clicca qui sotto per verificare la tua mail:
            </p>
            
            <a href='$link'
                style='
                background-color: lightgray;
                font-size: 14px;
                border: none;
                border-radius: 8px;
                padding: 10px 20px;
                text-decoration: none;
                color: black;
                display: inline-block
                '>
                VERIFICA
            </a>
        </div>";
        $mail->send();

    } catch (Exception $e) {
        
    }
}
?>
