<?php
namespace App\Controllers;

use PDO;
use Twig\Environment;
use App\Services\EmailService;
use Exception;

class EmailController extends BaseController
{
    public function __construct(PDO $pdo, Environment $twig, array $config)
    {
        parent::__construct($pdo, $twig, $config);
    }

    public function send()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $input = file_get_contents('php://input');
        $postData = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data received.']);
            return;
        }

        $to = $postData['recipientEmail'] ?? null;
        $subject = $postData['subject'] ?? 'Message from Website Contact Form';
        $message = $postData['message'] ?? null;
        
        $fromEmail = 'info@yourdomain.com'; 

        if (empty($to) || empty($message)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Recipient email and message cannot be empty.']);
            return;
        }

        try {
            if (!isset($this->config['resend']['api_key'])) {
                throw new Exception("Resend API key is missing in config.php. Please ensure it's set.");
            }

            $emailService = new EmailService($this->config);
            
            $htmlContent = nl2br(htmlspecialchars($message));
            $textContent = strip_tags($message);

            if ($emailService->sendEmail($to, $subject, $htmlContent, $textContent, $fromEmail)) {
                echo json_encode(['success' => true, 'message' => 'Email sent successfully!']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again later.']);
            }
        } catch (Exception $e) {
            error_log("Email sending error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'An internal server error occurred: ' . $e->getMessage()]);
        }
    }
}