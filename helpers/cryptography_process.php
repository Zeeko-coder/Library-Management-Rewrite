<?php

//cipher key - it should contsin 32 characters
define('SECRET_KEY', 'my_secret_key_123456');

//encryption function process
function encryptionData($data)
{
    $key = hash('sha256', SECRET_KEY);
    $iv = substr(hash('sha256', 'iv_secret'), 0, 16);

    return openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
}

//
function decryptionData($data)
{
    $key = hash('sha256', SECRET_KEY);
    $iv = substr(hash('sha256', 'iv_secret'), 0, 16);

    return openssl_decrypt($data, 'AES-256-CBC', $key, 0, $iv);
}
