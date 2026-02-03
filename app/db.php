<?php
//db() funkcija nodrošina vienotu 
// PDO savienojumu ar datubāzi, izmantojot konfigurācijas failu un izvairoties no atkārtotas savienojuma veidošanas.

//Šis fails nodrošina vienu kopīgu PDO savienojumu visam projektam, izmantojot lazy loading un Singleton principu

function db(): PDO {
    static $pdo = null; //savienojums tiek izveidots tikai vienu reizi
    if ($pdo) return $pdo; //Ja savienojums jau ir – atgriežam to

    $config = require __DIR__ . '/config.php';
    $c = $config['db'];  //Ielādē app/config.php Paņem tikai db sadaļu

    $dsn = "mysql:host={$c['host']};dbname={$c['name']};charset={$c['charset']}";  //DSN izveide
    $pdo = new PDO($dsn, $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}
