<?php

use Dreamstats\Controller\ATMatchController;
use Dreamstats\Controller\EventController;
use Dreamstats\Controller\IndexController;
use Dreamstats\Controller\LoginController;
use Dreamstats\Controller\MatchController;
use Dreamstats\Controller\PlayerController;
use Dreamstats\Service\ATMatchService;
use Dreamstats\Service\EventService;
use Dreamstats\Service\MatchService;
use Dreamstats\Service\PlayerService;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

if ($_SERVER['SERVER_NAME'] !== "localhost") {
    header('Strict-Transport-Security:max-age=31536000;');
}
require_once __DIR__ . '/../vendor/autoload.php';

$dbSettings = parse_url(getenv('DATABASE_URL'));

$config = [
    'displayErrorDetails' => $_SERVER['SERVER_NAME'] === "localhost"
];

$app = new App(['settings' => $config]);
$container = $app->getContainer();
$container['pdo'] = function () {
    $pdo = new PDO(sprintf("pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
        getenv('DB_HOST'), getenv('DB_PORT'), getenv('DB_NAME'), getenv('DB_USER'), getenv('DB_PWD')));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
};

$container['view'] = function ($container) {
    $view = new Twig(__DIR__ . '/../view', ['cache' => false]);
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new TwigExtension($container['router'], $basePath));

    return $view;
};

$container['playerService'] = function () use ($container) {
    return new PlayerService($container['pdo']);
};
$container['eventService'] = function () use ($container) {
    return new EventService($container['pdo']);
};
$container['matchService'] = function () use ($container) {
    return new MatchService($container['pdo']);
};
$container['atMatchService'] = function () use ($container) {
    return new ATMatchService($container['pdo']);
};

$app->get("/", IndexController::class . ":index");

$app->get('/at/new', ATMatchController::class . ':showAtMatchForm');
$app->post('/at/register', ATMatchController::class . ':registerNewATMatch');
$app->get('/at', ATMatchController::class . ":showAllAt");
$app->delete('/at/{id}', ATMatchController::class . ':deleteAtMatch');

$app->get("/player/new", PlayerController::class . ":showRegisterForm");
$app->post("/player/new", PlayerController::class . ":register");
$app->get("/player/{playerId}/vs/{enemyId}", PlayerController::class . ":compare");
$app->get("/player/{id}/edit", PlayerController::class . ":showEditForm");
$app->get("/player/{id}/at", ATMatchController::class . ":showPlayerATMatches");
$app->get("/player/{id}", PlayerController::class . ":show");

$app->get("/event/new", EventController::class . ":showRegisterPage");
$app->post("/event/new", EventController::class . ":register");
$app->get("/event/{id}", EventController::class . ":show");

$app->get("/match/new", MatchController::class . ":showRegisterForm");
$app->post("/api/match", MatchController::class . ":registerApi");
$app->get("/match/{id}/edit", MatchController::class . ":showEditForm");

$app->get("/login", LoginController::class . ":showLoginPage");
$app->post("/login", LoginController::class . ":login");
$app->post("/logout", LoginController::class . ":logout");

session_start();
$app->run();

function pre_print_r($x)
{
    echo "<pre>";
    print_r($x);
    echo "</pre>";
}