<?php

namespace Mestrona\Bank;

class BankTransactionTable extends Table
{
    /**
     * @param Transaction[] $transactions
     *
     * @return int Number of saved transactions
     */
    public function insertTransactions(array $keyedTransactions)
    {
        $saved = 0;

        foreach ($keyedTransactions as $id => $aqTransaction) {
            $row = [
                'id' => $id,
//                'date' => $aqTransaction->getDate()->format(Table::SQL_DATE_FORMAT),
                'valuta_date' => $aqTransaction->getValutaDate()->format(Table::SQL_DATE_FORMAT),
                'remote_account_holder_name' => $aqTransaction->getRemoteAccount()->getAccountHolderName(),
                'remote_account_number' => $aqTransaction->getRemoteAccount()->getAccountNumber(),
                'remote_bank_code' => $aqTransaction->getRemoteAccount()->getBankCode()->getString(),
                'local_account_holder_name' => $aqTransaction->getLocalAccount()->getAccountHolderName(),
                'local_account_number' => $aqTransaction->getLocalAccount()->getAccountNumber(),
                'local_bank_code' => $aqTransaction->getLocalAccount()->getBankCode()->getString(),
                'amount' => $aqTransaction->getValue()->getAmount() / 100,
                'currency' => $aqTransaction->getValue()->getCurrency()->getName(),
                'purpose' => $aqTransaction->getPurpose(),
            ];


            if ($this->idExists($id)) {
                continue;
            }

            $this->insertArray($row);
            $saved++;
        }

        return $saved;
    }

    public function getNewestTransactionDate()
    {
        $sql = "SELECT `date` FROM `".$this->table."` ORDER BY `date` DESC LIMIT 1";
        $statement = $this->databaseHandle->prepare($sql);
        $statement->execute();

        $column = $statement->fetchColumn();

        if (!$column) {
            return null;
        }

        return new \DateTime($column);
    }


}