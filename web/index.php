<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/ConfigResolver.php';

$dbSettings = parse_url(getenv('DATABASE_URL'));

$config = [
    'displayErrorDetails' => true
];

$app = new \Slim\App(['settings' => $config]);
$container = $app->getContainer();
$container['pdo'] = function () {
    $pdo = new PDO(sprintf("pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
        getenv('DB_HOST'), getenv('DB_PORT'), getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PWD')));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
};

$app->get("/", function (\Slim\Http\Request $req, \Slim\Http\Response $res) use ($app) {
    $pdo = $app->getContainer()['pdo'];
    $r = $pdo->query("SELECT * FROM test_table");
    foreach ($r as $x) {
        pre_print_r($x);
    }
    $res->getBody()->write("done");
});

$app->run();

function pre_print_r($x) {
    echo "<pre>";
    print_r($x);
    echo "</pre>";
}