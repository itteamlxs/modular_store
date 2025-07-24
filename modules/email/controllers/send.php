<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SendController
{
    public function send(): void
    {
        $to = $_POST['to'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $body = $_POST['body'] ?? '';

        if (empty($to) || empty($subject) || empty($body)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Servidor SMTP de Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'itbkup24@gmail.com';  // Tu correo electrónico de Gmail
            $mail->Password = 'hbar epnw rpbd cshw';  // Tu contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Remitente y destinatario
            $mail->setFrom('itbkup24@gmail.com', 'Modular Store');
            $mail->addAddress($to);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            echo json_encode(['success' => 'Email sent successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send email: ' . $mail->ErrorInfo]);
        }
    }
}