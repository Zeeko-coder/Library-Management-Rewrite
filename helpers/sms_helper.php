<?php

/**
 * UniSMS Helper
 * Handles interaction with UniSMS API for sending SMS messages.
 */

require_once __DIR__ . '/../config/sms_config.php';

/**
 * Sends an SMS using UniSMS API
 * 
 * @param string $recipient Phone number in E.164 format (e.g., +639123456789)
 * @param string $content Message content (max 160 characters)
 * @return array Response from the API [success => bool, message => string, data => array]
 */
function sendUniSMS($recipient, $content)
{
    if (UNISMS_API_KEY === 'YOUR_API_SECRET_KEY') {
        return [
            'success' => false,
            'message' => 'UniSMS API Key not configured. Please update config/sms_config.php'
        ];
    }

    // Sanitize the recipient number: remove all non-numeric characters
    $recipient = preg_replace('/[^0-9]/', '', $recipient);

    // Ensure the recipient number is in E.164 format (starts with +)
    if (!empty($recipient) && strpos($recipient, '+') !== 0) {
        $recipient = '+' . $recipient;
    }

    $payload = [
        'recipient' => $recipient,
        'content' => $content
    ];

    $ch = curl_init(UNISMS_API_URL);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    // Basic Auth: API_SECRET_KEY as username, empty password
    curl_setopt($ch, CURLOPT_USERPWD, UNISMS_API_KEY . ":");

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return [
            'success' => false,
            'message' => 'Connection error: ' . $error
        ];
    }

    $responseData = json_decode($response, true);

    if ($httpCode === 201) {
        return [
            'success' => true,
            'message' => 'SMS sent successfully',
            'data' => $responseData
        ];
    } else {
        // Try to get a specific message, otherwise fallback to the raw response or the HTTP code
        $errorMessage = $responseData['message'] ?? $responseData['error'] ?? null;
        
        if (!$errorMessage && $response) {
            $errorMessage = "API Error (HTTP $httpCode) for $recipient: " . $response;
        } elseif (!$errorMessage) {
            $errorMessage = "API Error (HTTP $httpCode) for $recipient";
        } else {
            $errorMessage = "Failed to send SMS to $recipient: " . $errorMessage;
        }

        return [
            'success' => false,
            'message' => $errorMessage,
            'data' => $responseData
        ];
    }
}
