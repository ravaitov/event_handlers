<?php

namespace App;

class PrevHandlerSwitch extends AnyEventSwitcher
{
    protected function protectRun(): void
    {
        $this->restWH->call(
            $this->method,
            [
                'event' => 'ONCRMCOMPANYUPDATE',
                'handler' => 'https://luna.zemser.ru/luna/event_handlers/change_of_assigned/changer.php'
            ]
        );
    }
}