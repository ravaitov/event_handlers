<?php

namespace App;

use App\Rest\RestToken;

class AnyEventSwitcher extends AbstractApp
{
    protected string $method;
    private string $event;

    public function __construct()
    {
        parent::__construct('Event switch');
        $this->restWH = new RestToken();
    }

    public function prepare(array $arg = []): void
    {
        $this->method =
            ($arg && $arg[0] === 'unbind')
            ? 'event.unbind'
            : 'event.bind';
        $this->event = $arg[1] ?? 'ONAPPTEST';
        $this->logger->log("method=$this->method, event=$this->event");
        $this->appName = "Event=$this->event $this->method";
    }

    protected function protectRun(): void
    {
        $this->restWH->call(
            $this->method,
            [
                'event' => $this->event,
                'handler' => 'https://app.zemser.ru:442/b24_events/' .$this->event
            ]
        );
    }

    protected function finish(): void
    {
        $this->logger->log('Ok ' . $this->method);
    }
}