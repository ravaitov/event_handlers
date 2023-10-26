<?php

namespace App\Rest;

use App\Config;
use App\Exceptions\AppException;
use App\Logger\Logger;
use GuzzleHttp\Client;
use PDO;

class RestToken extends RestWH
{
    private PDO $dataBase;

    public function __construct(int $timeOut = 20)
    {
        $this->logger = Logger::instance();
        $this->client = new Client([
            'base_uri' => 'https://bitrix.zemser.ru/rest/',
            'timeout' => $timeOut,
            'http_errors' => false,
            'verify' => false
        ]);

        $luna_db_hostname = $luna_db_username = $luna_db_password = $luna_db_dbname = '';
        include "/home/worker/secure/luna_db_data.php";
        $this->dataBase = new PDO("mysql:host=$luna_db_hostname;dbname=$luna_db_dbname", $luna_db_username, $luna_db_password);
    }

    private function token(): string
    {
        return $this->dataBase->query('select access_token from token where id = 17')->fetchObject()->access_token;
    }

    protected function response(string $method, array $params): \Psr\Http\Message\ResponseInterface
    {
        $params['auth'] = $this->token();
        $this->info = ['method' => $method, 'params' => $params];
        $res = $this->client->post($method, ['query' => $params]);
        if ($res->getStatusCode() !== 200 && json_decode($res->getBody())->error === 'invalid_token') { // возможно момент рефреша
            sleep(2);
            $params['auth'] = $this->token();
            $res = $this->client->post($method, ['query' => $params]);
        }
        return $res;
    }
}