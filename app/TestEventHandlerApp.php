<?php

namespace App;

use App\Rest\RestToken;

class TestEventHandlerApp extends AbstractApp
{
    public function __construct()
    {
        parent::__construct('Test event handler');
        $this->restWH = new RestToken();
    }

    public function prepare(array $params = []): void
    {
        if ('send' === ($params[0] ?? '')) {
            $this->restWH->call(
                'event.test',
                [
                    'any' => 'data'
                ]
            );
            exit();
        }
    }

    protected function protectRun(): void
    {
        $this->logger->log(var_export($_REQUEST, 1));
    }
}