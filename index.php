<?php
/**
 * PHP Reverse Proxy Cache
 */

require_once __DIR__ . '/autoloader.php';

$args = new \ReverseProxyCache\ArgumentParser($argv ?? [])->parse();
$errors = [];

// Clear cache
if (isset($args['clear-cache'])) {
  $files = array_filter(glob(__DIR__ . "/cache/{.,}*", GLOB_BRACE), function($file) {
    $basename = basename($file);
    return !in_array($basename, ['.', '..', '.gitkeep']);
  });
  foreach ($files as $file) {
    unlink($file);
  }

  echo "\033[32mCache cleared!\033[0m\n";
  exit;
}

if (!isset($args['origin'])) {
  $errors[] = 'origin argument is required';
}
elseif (!filter_var($args['origin'], FILTER_VALIDATE_URL)) {
  $errors[] = 'origin must be a valid URL';
}

if (!isset($args['port'])) {
  $errors[] = 'port argument is required';
}

if (!empty($errors)) {
  echo "\033[31m";
  echo "Errors detected: \n";
  foreach ($errors as $error) {
    echo "- $error\n";
  }
  echo "\033[0m";
  exit(1);
}

$origin = $args['origin'];
$port = $args['port'];

exec(
  sprintf(
    "CUSTOM_ORIGIN=%s php -S localhost:%s -t %s &",
    $origin,
    $port,
    __DIR__ . '/proxy'
  ),
  $output,
  $returnCode
);

if ($returnCode === 0) {
  echo "\033[32mPHP server started on http://localhost:{$port}\033[0m\n";
} else {
  echo "\033[31mFailed to start PHP server.\033[0m\n";
}