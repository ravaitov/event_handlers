<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;

Logger::instance()->echoLog = false;
$remote = $_REQUEST["auth"]["domain"] ?? "";
if ('bitrix.zemser.ru' !== $remote) {
    Logger::instance()->log("Недопустимый домен - $remote");
    die();
}

$event = explode('/', $_SERVER['REQUEST_URI'])[2] ?? '';
$class = [
        'ONCRMCOMPANYUPDATE' => 'CompanyUpdateHandler',
        'ONAPPTEST' => 'TestEventHandlerApp',
    ] [$event] ?? 'TestEventApp';

try {
    Logger::instance()->log("--- Event=$event -> $class REMOTE=" . $_SERVER['REMOTE_ADDR']);
    $app = eval("return new App\\$class();");
} catch (Throwable $t) {
    Logger::instance()->log("!!!Fatal\n" . $t->getMessage());
    exit();
}

try {
    $app->prepare([]);
    $app->run();
} catch (Throwable $t) {
    Logger::instance()->log("!!! Error: " . $t->getMessage() . "\nn");
    http_response_code(400);
}
