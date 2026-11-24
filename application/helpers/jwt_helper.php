<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('api_base64url_encode')) {
    function api_base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('api_base64url_decode')) {
    function api_base64url_decode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

if (!function_exists('api_jwt_encode')) {
    /**
     * @param array $payload
     * @param string $secret
     * @param int|null $ttl_seconds
     * @return string
     */
    function api_jwt_encode($payload, $secret, $ttl_seconds = null) {
        $header = api_base64url_encode(json_encode(array('typ' => 'JWT', 'alg' => 'HS256')));
        $now = time();
        $payload['iat'] = $now;
        if ($ttl_seconds !== null && $ttl_seconds > 0) {
            $payload['exp'] = $now + (int) $ttl_seconds;
        }
        $body = api_base64url_encode(json_encode($payload));
        $sig = hash_hmac('sha256', $header . '.' . $body, $secret, true);
        return $header . '.' . $body . '.' . api_base64url_encode($sig);
    }
}

if (!function_exists('api_jwt_decode')) {
    /**
     * @param string $jwt
     * @param string $secret
     * @return array|null
     */
    function api_jwt_decode($jwt, $secret) {
        if (!is_string($jwt) || $jwt === '') {
            return null;
        }
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }
        $sig = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $secret, true);
        if (!hash_equals(api_base64url_encode($sig), $parts[2])) {
            return null;
        }
        $payload = json_decode(api_base64url_decode($parts[1]), true);
        if (!is_array($payload)) {
            return null;
        }
        if (isset($payload['exp']) && (int) $payload['exp'] < time()) {
            return null;
        }
        return $payload;
    }
}
