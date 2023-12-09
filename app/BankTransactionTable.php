<?php

namespace Mestrona\Bank;

class BankTransactionTable extends Table
{
    const HOOK_PROCESS_ROW = 'process_row';
    const HOOK_AFTER_SAVED = 'after_saved';

    protected $hooks;

    public function __construct(\PDO $databaseHandle, string $table, string $primaryKey = 'id', $hooks = [])
    {
        parent::__construct($databaseHandle, $table, $primaryKey);
        $this->hooks = $hooks;
    }


    /**
     * @param Transaction[] $transactions
     *
     * @return int Number of saved transactions
     */
    public function insertTransactions(array $keyedTransactions, $configCode = 'default')
    {
        $saved = [];

        foreach ($keyedTransactions as $id => $aqTransaction) {
            $row = [
                $this->primaryKey => $id,
                'config_code' => $configCode,
                'date' => $aqTransaction->getDate()->format(Table::SQL_DATE_FORMAT),
                'valuta_date' => $aqTransaction->getValutaDate()->format(Table::SQL_DATE_FORMAT),
                'remote_account_holder_name' => $aqTransaction->getRemoteAccount()->getAccountHolderName(),
                'remote_account_number' => $aqTransaction->getRemoteAccount()->getAccountNumber(),
                'remote_bank_code' => $aqTransaction->getRemoteAccount()->getBankCode()->getString(),
                'local_account_holder_name' => $aqTransaction->getLocalAccount()->getAccountHolderName(),
                'local_account_number' => $aqTransaction->getLocalAccount()->getAccountNumber(),
                'local_bank_code' => $aqTransaction->getLocalAccount()->getBankCode()->getString(),
                'amount' => $aqTransaction->getValue()->getAmount() / 100,
                'currency' => $aqTransaction->getValue()->getCurrency()->getCode(),
                'purpose' => $aqTransaction->getPurpose(),
            ];

            if ($this->idExists($id, $configCode)) {
                continue;
            }

            if (isset($this->hooks[self::HOOK_PROCESS_ROW])) {
                $row = call_user_func($this->hooks[self::HOOK_PROCESS_ROW], $row);
            }

            $this->insertArray($row);
            $saved[] = $row;
        }

        if (isset($this->hooks[self::HOOK_AFTER_SAVED])) {
            call_user_func($this->hooks[self::HOOK_AFTER_SAVED], $saved);
        }

        return count($saved);
    }

    public function getNewestTransactionDate($configCode = 'default')
    {
        $sql = "SELECT `date` FROM `".$this->table."` WHERE config_code = ? ORDER BY `date` DESC LIMIT 1";
        $statement = $this->databaseHandle->prepare($sql);
        $statement->execute([$configCode]);

        $column = $statement->fetchColumn();

        if (!$column) {
            return null;
        }

        return new \DateTime($column);
    }


}
