<?php

namespace Mestrona\Bank;

use DateTime;

class App
{
    protected $accounts;

    public function __construct(array $accounts)
    {
        $this->accounts = $accounts;
    }

    public function run()
    {
        $configName = $argv[1] ?? 'default';

        $dbConfig = $this->accounts[$configName]['database'];

        $databaseHandle = new \PDO($dbConfig['pdoConnectionString'], $dbConfig['pdoUser'], $dbConfig['pdoPassword']);
        $databaseHandle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $table = new BankTransactionTable($databaseHandle, $dbConfig['table']);
        $newest = $table->getNewestTransactionDate();


        if ($newest == null) {
            $firstDay = null;
            echo 'Fetching all available transactions from the bank' . PHP_EOL;
        } else {
            $firstDay = clone $newest;
            $firstDay->modify('-10 day');
            echo sprintf('Newest transaction %s', $newest->format('Y-m-d') ) . PHP_EOL;
            echo sprintf('Fetching from %s', $firstDay->format('Y-m-d') ) . PHP_EOL;
        }

        $account = new Account($this->accounts[$configName]);

        echo 'Fetching ...' . PHP_EOL;

        $transactions = $account->fetchKeyedTransactions($firstDay);

        echo sprintf('Received %d transactions', count($transactions)) . PHP_EOL;

        echo 'Saving ...' . PHP_EOL;

        $countNew = $table->insertTransactions($transactions);

        echo sprintf('Saved %d NEW transactions', $countNew) . PHP_EOL;
    }


}