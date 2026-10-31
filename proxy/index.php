<?php
/**
 * Proxy service
 */

require_once __DIR__ . '/../autoloader.php';

$origin = getenv("CUSTOM_ORIGIN");

// Check for cache and serve if exists
$cacheKey = md5($origin);
$cacheFile = __DIR__ . '/../cache/' . $cacheKey;
$headersFile = __DIR__ . '/../cache/headers-' . $cacheKey . '.json';
if (file_exists($cacheFile) && file_exists($headersFile)) {
  $headers = json_decode(file_get_contents($headersFile), TRUE);
  foreach ($headers as $name => $value) {
    header("$name: $value");
  }
  header("X-PHP-Reverse-Proxy-Cache: HIT");
  echo file_get_contents($cacheFile);
  exit;
}

// Fetch from origin
$ch = curl_init($origin);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, TRUE);
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Reverse Proxy Cache Handler 1.0.0');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

$response = curl_exec($ch);

if ($response === FALSE) {
  die('cURL Error: ' . curl_error($ch));
}

$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headersStr = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

unset($ch);

// Parse headers
$headers = [];
foreach (explode("\r\n", $headersStr) as $line) {
  if (str_contains($line, ':')) {
    [$key, $value] = explode(':', $line, 2);
    $headers[trim($key)] = trim($value);
  }
}

file_put_contents($cacheFile, $body);
file_put_contents($headersFile, json_encode($headers));
foreach ($headers as $name => $value) {
  header("$name: $value");
}
header("X-PHP-Reverse-Proxy-Cache: MISS");
echo $body;