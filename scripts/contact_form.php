<?php
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

header('Content-Type: application/json');

$response = [];

if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $response['status'] = 'error';
    $response['message'] = 'PHPMailer is missing.';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $subject_input = htmlspecialchars(trim($_POST['subject']));
    $message = nl2br(htmlspecialchars(trim($_POST['message']))); // Preserve line breaks

    // Check required fields (all except message which might be optional)
    if (!$username || !$email || !$phone || !$subject_input) {
        $response['status'] = 'error';
        $response['message'] = 'Tous les champs requis doivent être remplis.';
        echo json_encode($response);
        exit;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'] ?? '';
        $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

        $mail->setFrom($email, $username);
        $mail->addAddress($_ENV['SMTP_TO_EMAIL'] ?? 'djamelhemch.pro@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'Nouveau message de contact';
        $mail->Body = "
            <div style='font-family:Arial, sans-serif; padding:10px; border:1px solid #ddd;'>
                <h2 style='color:#333;'>Nouveau message de contact</h2>
                <p><strong>📌 Nom:</strong> {$username}</p>
                <p><strong>📧 Email:</strong> {$email}</p>
                <p><strong>📞 Téléphone:</strong> {$phone}</p>
                <p><strong>📝 Sujet:</strong> {$subject_input}</p>
                <p><strong>📝 Message:</strong></p>
                <div style='background:#f9f9f9; padding:10px; border-radius:5px;'>{$message}</div>
            </div>
        ";
        $mail->AltBody = "Nom: $username\nEmail: $email\nTéléphone: $phone\nSujet: $subject_input\nMessage: $message";

        $mail->send();
        $response['status'] = 'success';
        $response['message'] = 'Message envoyé avec succès!';
    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = "Erreur lors de l'envoi du message: {$mail->ErrorInfo}";
    }
}

echo json_encode($response);
?>