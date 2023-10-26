<?php

namespace App;

use App\Rest\RestToken;
use App\Rest\RestWH;

class TestEventBindApp extends AbstractApp
{
    public function __construct()
    {
        parent::__construct('Test event bind');
        $this->restWH = new RestToken();
    }

    protected function protectRun(): void
    {
        $this->restWH->call(
            'event.bind',
            [
                'event' => 'ONAPPTEST',
                'handler' => 'https://app.zemser.ru/b24_events/ONAPPTEST'
            ]
        );
    }

    protected function finish(): void
    {
        $this->logger->log('Ok bind');
    }
}