<?php

require_once __DIR__ . '/vendor/autoload.php';

// Vonage API Configuration
// Replace these with your actual credentials from your Vonage Dashboard
if (!defined('VONAGE_API_KEY')) {
    define('VONAGE_API_KEY', 'API_KEY');
}
if (!defined('VONAGE_API_SECRET')) {
    define('VONAGE_API_SECRET', 'API_SECRET');
}
if (!defined('VONAGE_FROM_NUMBER')) {
    define('VONAGE_FROM_NUMBER', 'Vonage SMS API');
}

/**
 * Sends an OTP code via SMS using Vonage API
 * 
 * @param string $phoneNumber The recipient's phone number (international format, e.g., 639...)
 * @param string $otpCode The 6-digit OTP code
 * @return bool True if successful, false otherwise
 */
function sendOTPviaSMS($phoneNumber, $otpCode)
{
    try {
        $basic  = new \Vonage\Client\Credentials\Basic(VONAGE_API_KEY, VONAGE_API_SECRET);
        $client = new \Vonage\Client($basic);

        $messageText = "Your verification code is: $otpCode. It will expire in 5 minutes.";

        $sms = new \Vonage\SMS\Message\SMS(
            $phoneNumber,
            VONAGE_FROM_NUMBER,
            $messageText
        );

        $response = $client->sms()->send($sms);
        $message = $response->current();

        if ($message->getStatus() == 0) {
            return true;
        } else {
            // Log error if needed: $message->getErrorText()
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}
