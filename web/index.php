<?php

require_once __DIR__ . '/../vendor/autoload.php';
spl_autoload_register(function ($classname) {
    $path = __DIR__ . "/../src/model/" . $classname . ".php";
    if (file_exists($path)) {
        require_once $path;
    } elseif (file_exists(__DIR__ . "/../src/service/" . $classname . ".php")) {
        require_once __DIR__ . "/../src/service/" . $classname . ".php";
    } else {
        require_once(__DIR__ . "/../src/controller/" . $classname . ".php");
    }
});

$dbSettings = parse_url(getenv('DATABASE_URL'));

$config = [
    'displayErrorDetails' => $_SERVER['SERVER_NAME'] === "localhost"
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

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../src/view', ['cache' => false]);
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

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

$app->get("/", IndexController::class . ":index");

$app->get("/player/new", PlayerController::class . ":showRegisterForm");
$app->post("/player/new", PlayerController::class . ":register");
$app->get("/player/{playerId}/vs/{enemyId}", PlayerController::class . ":compare");
$app->get("/player/{id}/edit", PlayerController::class . ":showEditForm");
$app->get("/player/{id}", PlayerController::class . ":show");

$app->get("/event/new", EventController::class . ":showRegisterPage");
$app->post("/event/new", EventController::class . ":register");

$app->get("/match/new", MatchController::class . ":showRegisterForm");
$app->post("/api/match", MatchController::class . ":registerApi");
$app->get("/match/{id}/edit", MatchController::class . ":showEditForm");

$app->get("/login", LoginController::class . ":showLoginPage");
$app->post("/login", LoginController::class . ":login");
$app->post("/logout", LoginController::class . ":logout");

session_start();
$app->run();

function pre_print_r($x) {
    echo "<pre>";
    print_r($x);
    echo "</pre>";
}