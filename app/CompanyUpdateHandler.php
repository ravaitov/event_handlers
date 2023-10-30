<?php

namespace App;

class CompanyUpdateHandler extends AbstractApp
{
    private int $companyId;
    private int $assignedById; //ответственный за компанию
    private int $assignedByIdPrev = 0; //предыдущий ответственный за компанию

    public function __construct()
    {
        parent::__construct('Обработчик редактирования компании');
    }

    public function prepare(array $params = []): void
    {
        $requestDomain = $_REQUEST["auth"]["domain"] ?? "";
        if ('bitrix.zemser.ru' !== $requestDomain) {
            $this->logger->log("Недопустимый домен $requestDomain", Config::ERROR);
            $this->logAndDie();
        }
        $requestSscope = $_REQUEST["auth"]["scope"] ?? "";
        $this->companyId = $_REQUEST["data"]["FIELDS"]["ID"] ?? 0;
        if (!$this->companyId) {
            $this->logger->log('Нет ID компании', Config::ERROR);
            $this->logAndDie();
        }
        $this->appName .= " ID=$this->companyId";
        $scopes = explode(',', $requestSscope);
        if (!in_array('crm', $scopes)) {
            $this->logger->log("Неверный scope ($requestSscope)", Config::ERROR);
//            $this->logAndDie();
        }
        $userId = $_REQUEST["auth"]["user_id"] ?? 0;
        if (!$userId) {
            $this->logger->log('Нет ID пользователя', Config::ERROR);
            $this->logAndDie();
        }
        $this->appName .= " User=$userId";
    }

    private function logAndDie(): void
    {
//        $this->logger->log('$_REQUEST = ' . var_export($_REQUEST, 1));
        die();
    }

    protected function protectRun(): void
    {
        $company = $this->restWH->call(
            'crm.company.get',
            ['id' => $this->companyId]
        );
        $this->logger->log('Компания: ' . $company['TITLE'] . ', ID=' . $company['ID']);
        $this->assignedById = $company['ASSIGNED_BY_ID'] ?? 0;
        $companyPrev = $this->base->query(
            'select assigned from companies where company = ' . $this->companyId
        )->fetchArray(SQLITE3_NUM);
        if (!$companyPrev) {
            $this->insertCompany($company);
            $this->logger->log("Добавлена компания ID=$this->companyId");
        } else {
            $this->assignedByIdPrev = $companyPrev[0] ?? 0;
            if ($this->assignedByIdPrev === $this->assignedById) {
                $this->logger->log('Ответственный тот же ' . $this->assignedById);
                return;
            }
        }

        foreach ($this->getPrevAssignedList() as $contact) {
            $this->updateContact($contact['ID']);
        }
        $this->base->exec("update companies set assigned = $this->assignedById where company = $this->companyId");
    }

    private function getPrevAssignedList(): array
    {
        if (!$this->assignedByIdPrev) {
            $filter = ['COMPANY_ID' => $this->companyId];
            $this->logger->log('Нет предыддущего ответственного');
        } else {
            $filter = ['COMPANY_ID' => $this->companyId, 'ASSIGNED_BY_ID' => $this->assignedByIdPrev];
        }

        return $this->restWH->getBig(
            'crm.contact.list',
            [
                'filter' => $filter,
                'select' => ['ID', 'COMPANY_ID', 'ASSIGNED_BY_ID']
            ]
        );
    }

    private function updateContact(int $id): void
    {
        $res = $this->restWH->call(
            'crm.contact.update',
            [
                'id' => $id,
                'fields' => ['ASSIGNED_BY_ID' => $this->assignedById],
            ]
        );
        $this->logger->log("Контакт=$id, Ответственный $this->assignedByIdPrev -> $this->assignedById");
//        $this->logger->log('$_REQUEST = ' . var_export($_REQUEST, 1));
    }
}