<?php
date_default_timezone_set('Asia/Taipei');

// ← 這兩行一定要填上你專案的值 ←
$SUPABASE_URL = 'https://fdkhwqwtjentmuzwhokc.supabase.co';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M'; // 建議使用 Server Role

// callSupabase() & generateUUID() 這兩個函式保留不動
function callSupabase(string $endpoint, string $method = 'GET', array $data = null): array {
    global $SUPABASE_URL, $SUPABASE_KEY;
    $url = "{$SUPABASE_URL}/rest/v1/{$endpoint}";
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "apikey: {$SUPABASE_KEY}",
      "Authorization: Bearer {$SUPABASE_KEY}",
      "Content-Type: application/json",
      "Prefer: return=representation",
    ]);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status'=>$code,'body'=>json_decode($resp,true)];
}

function generateUUID(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
