<?php

namespace App\Services;

use GuzzleHttp\Client;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;

class BrevoService
{
    protected $apiInstance;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', config('services.brevo.key'));
        $this->apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );
    }

    public function sendEmail($to, $subject, $message, $params = [])
    {
        $data = [
            'subject' => $subject,
            'sender' => ['name' => 'Solo Park', 'email' => 'dodap2020@gmail.com'], 
            'replyTo' => ['name' => 'Solo Park', 'email' => 'dodap2020@gmail.com'],
            'to' => [['email' => $to]],
            'htmlContent' => $message,
        ];

        if (!empty($params)) {
            $data['params'] = (object)$params;
        }

        $sendSmtpEmail = new SendSmtpEmail($data);

        try {
            $this->apiInstance->sendTransacEmail($sendSmtpEmail);
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Brevo Email Error: ' . $e->getMessage());
            return false;
        }
    }
}
