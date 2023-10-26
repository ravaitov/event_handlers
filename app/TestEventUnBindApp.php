<?php

namespace App;

use App\Rest\RestToken;

class TestEventUnBindApp extends AbstractApp
{
    public function __construct()
    {
        parent::__construct('Test event unbind');
        $this->restWH = new RestToken();
    }

    protected function protectRun(): void
    {
        $this->restWH->call(
            'event.unbind',
            [
                'event' => 'ONAPPTEST',
                'handler' => 'https://app.zemser.ru/b24_events/ONAPPTEST'
            ]
        );
    }

    protected function finish(): void
    {
        $this->logger->log('Ok unbind');
    }

}