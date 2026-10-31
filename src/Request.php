<?php

namespace ReverseProxyCache;

final class Request {
  public string $method;
  public string $uri;
  public array $query;
  public array $body;
  public array $headers;

  public function __construct() {
    $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $this->query = $_GET ?? NULL;
    $this->headers = $this->getHeaders();
    $this->body = $this->parseBody();
  }

  private function getHeaders(): array {
    $headers = [];
    foreach ($_SERVER as $key => $value) {
      if (str_starts_with($key, 'HTTP_')) {
        $name = substr($key, 5)
            |> (fn($x) => str_replace('_', ' ', $x))
            |> strtolower(...)
            |> ucwords(...)
            |> (fn($x) => str_replace(' ', '-', $x));
        $headers[$name] = $value;
      }
    }

    return $headers;
  }

  private function parseBody(): array {
    // If it's a standard form submission
    if (
      $this->method === 'POST'
      && isset($_SERVER['CONTENT_TYPE'])
      && str_contains($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded')
    ) {
      return $_POST;
    }

    // If it's a JSON payload (e.g., API requests)
    $jsonData = json_decode(
      file_get_contents('php://input'),
      TRUE
    );

    return is_array($jsonData) ? $jsonData : $_POST;
  }

  public function input(string $key, $default = NULL): mixed {
    return $this->body[$key] ?? $this->query[$key] ?? $default;
  }
}