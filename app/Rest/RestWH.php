<?php

namespace App\Rest;

use GuzzleHttp\Client;
use App\Exceptions\AppException;
use App\Logger\Logger;
use App\Config;

class RestWH
{
    protected int $total;

    protected Client $client;

    protected array $error = [];

    protected array $info;

    protected Logger $logger;

    public function __construct(int $timeOut = 20)
    {
        $this->logger = Logger::instance();
        $this->client = new Client([
            'base_uri' => require '/home/worker/x_config/wh.php',
            'timeout' => $timeOut,
            'http_errors' => false,
            'verify' => false
        ]);
    }

    public function error(): array
    {
        return $this->error;
    }

    // наиболее быстрый вариант для count > 50, но не всегда. !!! >= <= - осторожно! Нечисловое поле ($orderedField) - осторожно!
    public function getBigOrdered(string $method, array $params, bool $orderAsc = true, string $orderedField = 'ID'): array
    {
        $this->info = ['method' => $method, 'params' => $params];
        $start = $orderAsc ? 0 : PHP_INT_MAX;
        $res = [];
        $params = array_change_key_case($params, CASE_LOWER);
        $filter = $params['filter'] ?? [];
        $filterKey = ($orderAsc ? '>' : '<') . $orderedField;
        if (isset($filter[$filterKey])) {
            $start = $filter[$filterKey];
            if ($orderAsc) {
                $nextStart = function ($next) use ($start) {
                    return max($start, $next);
                };
            } else {
                $nextStart = function ($next) use ($start) {
                    return min($start, $next);
                };
            }
        } else {
            $nextStart = function (int $next) {
                return $next;
            };
        }
        $params['start'] = -1;

        while (true) {
            $filter[$filterKey] = $start;
            $params['filter'] = $filter;
            $result = $this->call($method, $params);
            $res = array_merge($res, $result ?? []);
            if (count($result) < 50 || $this->error)
                break;
            $start = $nextStart(end($result)[$orderedField]);
        }

        return $res;
    }

    public function getBig(string $method, array $params): array
    {
        $this->info = ['method' => $method, 'params' => $params];

        $start = 0;
        $res = [];

        while (true) {
            $params['start'] = $start;
            $start += 50;
            $result = $this->call($method, $params);
            $res = array_merge($res, $result ?? []);
            if ($start >= $this->total || $this->error)
                break;
        }

        return $res;
    }

    protected function response(string $method, array $params): \Psr\Http\Message\ResponseInterface
    {
        return  $this->client->post($method, ['query' => $params]);
    }

    public function call(string $method, array $params, bool $ignoreError = false): mixed
    {
        $this->info ??= ['method' => $method, 'params' => $params];
        $this->error = [];

//        $this->logger->log($method);
//        $this->logger->log(print_r($params, 1));
//        return [];

        $response = $this->response($method,  $params);
        $result = json_decode($response->getBody(), true);
        $this->total ??= $result['total'] ?? 0;

        if ($response->getStatusCode() !== 200) {
            $this->error = [
                'status' => $response->getStatusCode(),
                'error' => $result['error'] ?? '?',
                'call' => $this->info,
            ];
            if (!$ignoreError)
                throw new AppException(print_r($this->error, 1));
            $this->logger->log(print_r($this->error, 1), Config::ERROR);
        }

        return $result['result'] ?? [];
    }
}