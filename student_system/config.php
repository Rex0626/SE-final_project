<?php
// config.php
// 不使用 SDK，改用 REST API 呼叫 Supabase

// 設定時區
date_default_timezone_set('Asia/Taipei');

// ← 在這裡填上你的專案 URL & Key ←
$SUPABASE_URL = 'https://fdkhwqwtjentmuzwhokc.supabase.co';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

/**
 * 向 Supabase REST API 發送請求
 *
 * @param string $endpoint  API 路徑 (e.g. "Works", "Participants?select=*")
 * @param string $method    HTTP 方法 (GET, POST, PATCH, DELETE)
 * @param array|null $payload  JSON 資料 (insert/update 時使用)
 * @return array  回傳陣列，包含 'status' (HTTP code) 和 'body' (解析後資料)
 */
function supabaseRequest(string $endpoint, string $method = 'GET', array $payload = null): array {
    $url = "{$GLOBALS['SUPABASE_URL']}/rest/v1/{$endpoint}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: {$GLOBALS['SUPABASE_KEY']}",
        "Authorization: Bearer {$GLOBALS['SUPABASE_KEY']}",
        "Content-Type: application/json",
    ]);

    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $code,
        'body'   => json_decode($body, true),
    ];
}

/**
 * UUID 產生函式（用於手動指定 PK）
 *
 * @return string  UUID v4 格式字串
 */
function generateUUID(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
