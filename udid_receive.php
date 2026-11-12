<?php
// 1. 讀取 iOS 裝置安裝描述檔後 POST 過來的原始簽章二進位資料
$rawData = file_get_contents('php://input');

if (empty($rawData)) {
    header('Location: https://h18.tw', true, 302);
    exit;
}

// 2. 從簽章二進位資料中擷取完整的 <plist>...</plist> XML 區塊並解析
$xmlStart = strpos($rawData, '<?xml');
$xmlEnd = strpos($rawData, '</plist>');

$udid = '';
$version = '';
$product = '';

if ($xmlStart !== false && $xmlEnd !== false) {
    $xmlString = substr($rawData, $xmlStart, ($xmlEnd - $xmlStart) + 8);
    $xml = @simplexml_load_string($xmlString);
    
    if ($xml && isset($xml->dict->key)) {
        $keys = $xml->dict->key;
        $strings = $xml->dict->string;
        for ($i = 0; $i < count($keys); $i++) {
            $k = (string)$keys[$i];
            $v = isset($strings[$i]) ? (string)$strings[$i] : '';
            if ($k === 'UDID') $udid = $v;
            if ($k === 'VERSION') $version = $v;
            if ($k === 'PRODUCT') $product = $v;
        }
    }
}

// 3. 組合小寫的 GET 網址參數，透過 HTTP 301 讓 iPhone 自動跳轉至您的主站
$query = http_build_query([
    'udid' => $udid,
    'version' => $version,
    'product' => $product
]);

header('Location: https://h18.tw/vercel_udid_receive.php?' . $query, true, 301);
exit;
