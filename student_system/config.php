<?php
date_default_timezone_set('Asia/Taipei');

$SUPABASE_URL = '';
$SUPABASE_KEY = '';
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function supabaseRequest($endpoint, $method = 'POST', $payload = null) {
    global $SUPABASE_URL, $SUPABASE_KEY;
    $ch = curl_init("{$SUPABASE_URL}/rest/v1/{$endpoint}");
    $headers = [
        "apikey: {$SUPABASE_KEY}",
        "Authorization: Bearer {$SUPABASE_KEY}",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    } elseif ($method === 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    }

    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [ $code, json_decode($raw, true) ];
}
?>
