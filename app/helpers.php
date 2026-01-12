<?php

function h(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $to): void {
  header('Location: ' . $to);
  exit;
}


function render(string $view, array $data, string $title): void {
  extract($data);
  ob_start();
  require __DIR__ . "/../views/{$view}";
  $content = ob_get_clean();
  require __DIR__ . "/../views/layout.php";
}
