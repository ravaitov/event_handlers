<?php

namespace App;

class RestTest extends AbstractApp
{
    protected function protectRun(): void
    {
//        $res = $this->restWH->call(
//            'crm.contact.update',
//            [
//                'id' => 105388,
//                'fields' => ['ASSIGNED_BY_ID' => 824], //824 2677
//            ]
//        );
//        print_r($res);
//        exit();
        $res = $this->restWH->getBig(
            'crm.contact.list',
            [
                'filter' => ['COMPANY_ID' => 1753, 'ASSIGNED_BY_ID' => 2822],
                'select' => ['ID', 'LAST_NAME', 'COMPANY_ID', 'ASSIGNED_BY_ID']
            ]
        );
//        $res = $this->restWH->call(
//            'crm.contact.get',
//            ['id' => 7614]
//        );
        $this->logger->log(print_r($res, 1));

    }
}