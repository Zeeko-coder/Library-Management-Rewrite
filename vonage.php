<?php

$basic  = new \Vonage\Client\Credentials\Basic("API_KEY", "API_SECRET");
$client = new \Vonage\Client($basic);

$sms = new \Vonage\SMS\Message\SMS(
    639631667321,
    "Vonage SMS API",
    "Hello from Vonage SMS API"
);

$client->message()->send($sms);
