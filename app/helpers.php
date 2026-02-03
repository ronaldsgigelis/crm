<?php

//h() izmantoju visur, kur rādu lietotāja datus HTML, lai nebūtu XSS
//pārveido lietotāja ievadi drošā tekstā, lai HTML netiktu izpildīts
//lai pasargātos no XSS (ja DB vai forma satur, piemēram, <script>...)
function h(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

//aizsūta lietotāju uz citu URL.
//Pēc datu saglabāšanas ar POST es taisu redirect uz GET lapu — PRG patterns.
//header() strādā tikai tad, ja pirms tam nav izdrukāts HTML/echo
function redirect(string $to): void {
  header('Location: ' . $to);
  exit;
}

//Kontrolieris sagatavo datus un izsauc render(). Render ielādē konkrēto view iekš bufera, iegūst HTML kā $content, 
//un pēc tam to ieliek kopējā layout.
function render(string $view, array $data, string $title): void {
  extract($data);
  ob_start();
  require __DIR__ . "/../views/{$view}";
  $content = ob_get_clean();
  require __DIR__ . "/../views/layout.php";
}
