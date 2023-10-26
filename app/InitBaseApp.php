<?php

namespace App;

class InitBaseApp extends AbstractApp
{
    public function __construct()
    {
        parent::__construct('Заполнение базы');
    }

    protected function protectRun(): void
    {
        $this->base->exec('delete from companies');
        $res = $this->restWH->getBig(
            'crm.company.list',
            [
                'select' => ['ID', 'TITLE', 'ASSIGNED_BY_ID'],
//                'filter' => [],
            ]
        );
        foreach ($res as $i) {
            $this->insertCompany($i);
        };
    }
}