<?php

namespace App;

use SQLite3;
use App\Logger\Logger;
use App\Rest\RestWH;
use SQLite3Stmt;

abstract class AbstractApp
{
    protected Config $config;
    protected SQLite3 $base;
    protected Logger $logger;
    protected string $appName;
    protected int $status = 400;
    protected array $params;
    protected array|bool $row;
    protected int $id;
    protected RestWH $restWH;
    protected SQLite3Stmt $stmt;


    public function __construct($appName = 'x')
    {
        $this->config = Config::instance();
        $this->appName = $appName;
        $this->config->setParam('app_name', $this->appName);
        $this->logger = Logger::instance();
        $this->base = $this->config->dataBase();
        $this->restWH = new RestWH();
        $this->logger->log(">>> Старт: " . $this->appName . '. V=' . $this->config->conf('version'), Config::EVENT);
    }

    public function __destruct()
    {
        $this->logger->log('<<< Завершение: ' . $this->appName . "\n", Config::EVENT);
        $this->base->close();
    }

    public function prepare(array $params = []): void
    {
    }


    public function run(array $params = []): void
    {
        $this->params = $params;
        try {
            $this->protectRun();
            $this->status = 200;
            $this->finish();
        } catch (Exceptions\AppException $exception) {
            $this->logger->log($exception->getMessage(), Config::ERROR);
            $this->status = 400;
        } catch (\Throwable $exception) {
            $this->logger->log($exception->getMessage(), Config::ERROR);
            $this->status = 400;
        }
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    protected function insertCompany(array $res): void
    {
        $this->stm ??= $this->base->prepare('insert into companies(company, assigned, name) values(?, ?, ?)');
        $this->stm->bindParam(1, $res['ID']);
        $this->stm->bindParam(2, $res['ASSIGNED_BY_ID']);
        $this->stm->bindParam(3, $res['TITLE']);
        $this->stm->execute();
    }

    protected function protectRun(): void
    {
    }

    protected function finish(): void
    {
    }
}