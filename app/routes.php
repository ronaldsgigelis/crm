<?php

function base_path(): string {
  // piem.: /crm/public
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
  return $base === '.' ? '' : $base;
}

  //Paņem tikai ceļu no URL (bez query string)
function path(): string {
  $uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
  $base = base_path();

  // noņemam /crm_mvp_mysql/public prefiksu
  if ($base && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
  }

  // normalizējam
  $uri = rtrim($uri, '/') ?: '/';

  // ja atver tieši /index.php, uzskatām par /
  if ($uri === '/index.php') $uri = '/';

  return $uri;
}

function is_route(string $method, string $p): bool {
  return $_SERVER['REQUEST_METHOD'] === $method && path() === $p;
}

function url(string $p): string {
  $p = ($p === '') ? '/' : $p;
  return base_path() . $p;
}

