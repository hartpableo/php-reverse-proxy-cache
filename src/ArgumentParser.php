<?php

namespace ReverseProxyCache;

class ArgumentParser {
  public array $argv = [];

  public function __construct(array $argv) {
    $this->argv = $argv;
  }

  public function parse(): array {
    $parsed = [];
    foreach ($this->argv as $arg) {
      $arg = str_replace(['\'', '"'], '', trim($arg));
      $isValid = str_starts_with($arg, '--');

      if (!$isValid) {
        continue;
      }

      $arg = array_map('trim', explode('=', $arg, 2));
      $key = str_replace('--', '', $arg[0]);
      $parsed[$key] = $arg[1] ?? TRUE;
    }

    return $parsed;
  }
}