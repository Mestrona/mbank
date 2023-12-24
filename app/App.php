<?php

namespace Mestrona\Bank;

use DateTime;

class App
{
    const HOOK_SUMMARY = 'summary';
    protected $accounts;

    public function __construct(array $accounts)
    {
        $this->accounts = $accounts;
    }

    public function run()
    {
        global $argv;

        $configParam = $argv[1] ?? 'default';

        if ($configParam == '--all') {
            foreach(array_keys($this->accounts) as $configCode)  {
                $this->fetchWithConfig($configCode);
            }
        } else {
            $this->fetchWithConfig($configParam);
        }
    }

    /**
     * Fetch transactions for a specific config name
     *
     * @param string $configCode
     */
    private function fetchWithConfig(string $configCode)
    {
        $dbConfig = $this->accounts[$configCode]['database'];

        $databaseHandle = new \PDO($dbConfig['pdoConnectionString'], $dbConfig['pdoUser'], $dbConfig['pdoPassword']);
        $databaseHandle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $table = new BankTransactionTable($databaseHandle,
            $dbConfig['table'],
            $dbConfig['primaryKey'] ?? 'id',
            $this->accounts[$configCode]['hooks'] ?? []
        );
        $newest = $table->getNewestTransactionDate($configCode);

        echo 'Config code = ' . $configCode . PHP_EOL;

        if ($newest == null) {
            $firstDay = new DateTime();
            $firstDay->modify('-21 day');
            echo 'Fetching all available transactions from the bank' . PHP_EOL;
        } else {
            $firstDay = clone $newest;
            $firstDay->modify('-1 day');
            echo sprintf('Newest transaction %s', $newest->format('Y-m-d') ) . PHP_EOL;
            echo sprintf('Fetching from %s', $firstDay->format('Y-m-d') ) . PHP_EOL;
        }

        $account = new Account($this->accounts[$configCode]);

        echo 'Fetching ...' . PHP_EOL;

        $all = $account->fetchAll($firstDay);

        $transactions = $account->keyTransactions($all->getTransactions());

        echo sprintf('Received %d transactions', count($transactions)) . PHP_EOL;

        echo 'Saving ...' . PHP_EOL;

        $savedTransactions = $table->insertTransactions($transactions, $configCode);

        echo sprintf('Saved %d NEW transactions', count($savedTransactions)) . PHP_EOL;

        $finalBalance = $account->getFinalBalance($all->getBalances());

        echo sprintf('Final Balance: %s', $finalBalance);

        if (isset($this->accounts[$configCode]['hooks'][self::HOOK_SUMMARY])) {
            call_user_func($this->accounts[$configCode]['hooks'][self::HOOK_SUMMARY], $savedTransactions, $finalBalance);
        }
    }


}
