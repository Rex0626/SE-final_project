<?php
// db_connect.php

// API Key 與 Base URL 統一管理
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$baseUrl = "https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1";

/**
 * 執行 Supabase API 請求並取得 JSON 轉陣列結果
 *
 * @param string $endpoint API 路徑 (例如 Participants?select=*)
 * @return array|null 回傳結果陣列，失敗回傳 null
 */
function supabaseRequest(string $endpoint): ?array {
    global $apiKey, $baseUrl;

    $url = $baseUrl . '/' . $endpoint;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log("cURL Error: $err");
        return null;
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error: " . json_last_error_msg());
        return null;
    }

    return $data;
}
