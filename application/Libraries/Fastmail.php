<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use System\Libraries\Logger;

class Fastmail
{
    protected $mailer;
    protected $config;
    protected $theme;

    public function __construct($config = [])
    {
        // Get email config from config file if no custom config provided
        $option_email = option('email');
        $option_email = is_array($option_email) ? $option_email : json_decode($option_email, true) ?? [];
        $option_email = array_column($option_email, 'email_value', 'email_key');
        $this->config = !empty($config) ? $config : $option_email;
        $this->theme = config('theme'); // Get current theme from config
        $this->mailer = new PHPMailer(true); // Create an instance of PHPMailer
        $this->_setup();
    }

    /**
     * Setup configuration for PHPMailer
     */
    protected function _setup()
    {
        try {
            // Setup SMTP server configuration
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['mail_host'] ?? '';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['mail_username'] ?? '';
            $this->mailer->Password = $this->config['mail_password'] ?? '';
            $this->mailer->SMTPSecure = $this->config['mail_encryption'] ?? 'tls';
            $this->mailer->Port = $this->config['mail_port'] ?? 587;
            $this->mailer->CharSet  = $this->config['mail_charset'] ?? 'UTF-8';

            // Set default sender address
            if (!empty($this->config['mail_from_address'])) {
                $this->mailer->setFrom($this->config['mail_from_address'], $this->config['mail_from_name'] ?? '');
            }
        } catch (Exception $e) {
            throw new \System\Core\AppException('Fastmail Setup Error: ' . $e->getMessage());
        }
    }

    /**
     * Send email with content
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $content Email content (HTML or plain text)
     * @param array $options Additional options
     * 
     * Options array can contain:
     * - 'cc' => array() - CC recipients
     * - 'bcc' => array() - BCC recipients  
     * - 'attachments' => array() - File paths to attach
     * - 'isHtml' => bool - Whether content is HTML (default: true)
     * - 'smtpDebug' => int - SMTP debug level (0-4, default: 0)
     * 
     * Example usage:
     * $mail->send('user@example.com', 'Test Subject', '<h1>Hello World</h1>', [
     *     'cc' => ['cc@example.com'],
     *     'bcc' => ['bcc@example.com'],
     *     'attachments' => ['/path/to/file.pdf'],
     *     'isHtml' => true,
     *     'smtpDebug' => 0
     * ]);
     * 
     * @return bool Returns true if sent successfully, false if failed
     */
    public function send($to, $subject, $content, $options = [])
    {
        try {
            // Set default options for common configurations
            $defaultOptions = [
                'isHtml' => true,
                'smtpDebug' => 0,
                'cc' => [],
                'bcc' => [],
                'attachments' => []
            ];
            
            // Merge user options with defaults
            $options = array_merge($defaultOptions, $options);
            
            $this->mailer->clearAllRecipients(); // Clear all recipients before adding new
            $this->mailer->addAddress($to);

            // Add CC if exists
            if (!empty($options['cc']) && is_array($options['cc'])) {
                foreach ($options['cc'] as $cc) {
                    $this->mailer->addCC($cc);
                }
            }

            // Add BCC if exists
            if (!empty($options['bcc']) && is_array($options['bcc'])) {
                foreach ($options['bcc'] as $bcc) {
                    $this->mailer->addBCC($bcc);
                }
            }

            // Add attachments if exists
            if (!empty($options['attachments']) && is_array($options['attachments'])) {
                foreach ($options['attachments'] as $file) {
                    $this->mailer->addAttachment($file);
                }
            }

            // Set SMTP debug level
            $this->mailer->SMTPDebug = $options['smtpDebug'];

            // Set subject and content
            $this->mailer->isHTML($options['isHtml']);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $content;
            // Send email
            return $this->mailer->send();
        } catch (Exception $e) {
            // Log error if needed
            Logger::error($e->getMessage(), $e->getFile(), $e->getLine());
            return false;
        }
    }

}