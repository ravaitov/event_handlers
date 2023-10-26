<?php

namespace App;

use App\Exceptions\AppException;
use App\Traits\SingletonTrait;
use SQLite3;

class Config
{
    use SingletonTrait;

    /**
     * from table zsmicroapp.applog_levels
     */
    const ERROR = 1;
    const WARNING = 2;
    const IMPORTANT = 3;
    const EVENT = 4;
    const DEBUG = 5;

    const LOG_DB = [self::ERROR, self::WARNING, self::IMPORTANT, self::EVENT]; // levels logged into DB

    const APP_ID = 0;

    public array $level_names = [
        0 => '',
        self::ERROR => 'Ошибка: ',
        self::WARNING => 'Предупреждение: ',
        self::IMPORTANT => 'Важно! ',
        self::EVENT => '',
        self::DEBUG => 'Отладка: ',
    ];

    private SQLite3 $dataBase;

    private array $conf = [
        'version' => '0.2.0',
        'comment' => '',
        'log_file' => '??', // auto init
        'log_limit' => 90, // log files count limit
        'pending_errors' => '??', // auto init
        'app_id' => self::APP_ID, // !!!
        'database' => 'sqlite.db'
    ];

    public function dataBase(): SQLite3
    {
        $this->dataBase ??= new SQLite3($this->conf('database'));
        return $this->dataBase;
    }

    public function conf(string $key): array|string|int
    {
        if (!isset($this->conf[$key])) {
            throw new AppException("Config error! Unknown key='$key'", true);
        }

        return $this->conf[$key];
    }


    public function setParam(string $key, $param): void
    {
        $this->conf[$key] = $param;
    }

    public function appName(): string
    {
        return $this->conf['app_names'][$this->conf['app_id'] ?? 0] ?? '';
    }

    protected function init(): void
    {
        date_default_timezone_set('Europe/Moscow');
        try {
            $this->conf['database'] = realpath(__DIR__ . '/../storage/') . '/' . $this->conf['database'];
            $this->conf['log_dir'] = realpath(__DIR__ . '/../log/') . '/';
            $this->conf['log_file'] = $this->conf['log_dir'] . 'log_%s.txt';
        } catch (\Throwable $t) {
            throw new AppException("Config init error! " . $t->getMessage(), true);
        }
    }
}