<?php
namespace App\Services;

use Resend\Resend as ResendClient;
use Exception;

class EmailService
{
    private ResendClient $resend;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        if (!isset($this->config['resend']['api_key'])) {
            throw new Exception('Resend API key is not configured.');
        }
        $this->resend = ResendClient::client($this->config['resend']['api_key']);
    }

    public function sendEmail(string $to, string $subject, string $htmlContent, string $textContent = '', string $from = 'onboarding@resend.dev'): bool
    {
        try {
            $this->resend->emails->send([
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'html' => $htmlContent,
                'text' => $textContent,
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Resend Email Error: ' . $e->getMessage());
            return false;
        }
    }
}