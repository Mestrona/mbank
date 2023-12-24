<?php

namespace Mestrona\Bank;

use AqBanking\Account as AqAccount;
use AqBanking\AccountMatcher;
use AqBanking\Bank;
use AqBanking\BankCode;
use AqBanking\Command\AddAccountFlagsCommand;
use AqBanking\Command\AddUserCommand;
use AqBanking\Command\AddUserFlagsCommand;
use AqBanking\Command\GetAccountsCommand;
use AqBanking\Command\GetAccSepaCommand;
use AqBanking\Command\GetSysIDCommand;
use AqBanking\Command\ListAccounts;
use AqBanking\Command\ListAccountsCommand;
use AqBanking\Command\ListUsersCommand;
use AqBanking\Command\RenderContextFileToXMLCommand;
use AqBanking\Command\RequestCommand;
use AqBanking\Command\SetITanModeCommand;
use AqBanking\ContextFile;
use AqBanking\ContextXmlRenderer;
use AqBanking\HbciVersion;
use AqBanking\PinFile\PinFile;
use AqBanking\PinFile\PinFileCreator;
use AqBanking\Transaction;
use AqBanking\User;
use AqBanking\UserMatcher;
use DateTime;
use PDO;

class Account
{
    protected $config;
    protected $bankCode;
    protected $hbciVersion;
    protected $bank;
    protected $account;
    protected $user;
    protected $existingUser;
    protected $pinFile;

    public function __construct($config)
    {
        $this->config = $config;

        $this->bankCode = new BankCode($this->config['bank']['bankCode']);
        $this->hbciVersion = new HbciVersion($this->config['bank']['hbciVersion']);
        $this->bank = new Bank($this->bankCode, $this->config['bank']['bankUrl'], $this->hbciVersion);
        $this->account = new AqAccount($this->bankCode, $this->config['accountNumber']);
        $this->user = new User($this->config['userId'], $this->config['accountOwner'], $this->bank);
        $this->pinFile = new PinFile($this->getStoragePath(), $this->user);
    }

    public function fetchAll(DateTime $from = null)
    {
        $this->initializeAqBanking();

        $contextFile = new ContextFile($this->getStoragePath() . '/aqBanking.ctx');

        $runRequest = new RequestCommand($this->account, $contextFile, $this->pinFile);
        $runRequest->execute($from);

        $render = new RenderContextFileToXMLCommand();
        $dom = $render->execute($contextFile);
        $result = new ContextXmlRenderer($dom);

        return $result;
    }

    public function fetchTransactions(DateTime $from = null)
    {
        $result = $this->fetchAll($from);
        $transactions = $result->getTransactions();

        return $transactions;
    }

    public function fetchKeyedTransactions(DateTime $from = null)
    {
        $keyedTransactions = $this->keyTransactions($this->fetchTransactions($from));

        return $keyedTransactions;
    }

    protected function initializeAqBanking()
    {
        $listUsers = new ListUsersCommand();
        $userList = $listUsers->execute();
        $userMatcher = new UserMatcher($userList);
        $this->existingUser = $userMatcher->getExistingUser($this->user);

        if ($this->existingUser === null) {
            $addUser = new AddUserCommand();
            $addUser->execute($this->user);

            $userList = $listUsers->execute();
            $userMatcher = new UserMatcher($userList);
            $this->existingUser = $userMatcher->getExistingUser($this->user);

            if ($this->existingUser === null) {
                throw new \RuntimeException('User not found, even after creating');
            }
        }

        $createPinFile = new PinFileCreator($this->getStoragePath());
        $createPinFile->createFile($this->config['pin'], $this->user);

        $getSysId = new GetSysIDCommand();
        $getSysId->execute($this->existingUser, $this->pinFile);

        $setITanMode = new SetITanModeCommand();
        $setITanMode->execute($this->existingUser, $this->config['tanMode']);

        $getAccounts = new GetAccountsCommand();
        $getAccounts->execute($this->existingUser, $this->pinFile);

        $listAccounts = new ListAccountsCommand();
        $accountList = $listAccounts->execute();
        $accountMatcher = new AccountMatcher($accountList);
        $existingAccount = $accountMatcher->getExistingAccount($this->account);

        if (is_null($existingAccount)) {
            throw new \Exception('Account not found in AqBanking');
        }

        $addAccountFlags = new AddAccountFlagsCommand();
        $addAccountFlags->execute($existingAccount, AddAccountFlagsCommand::FLAG_PREFER_CAMT_DOWNLOAD);

        $getAccSepa = new GetAccSepaCommand();
        $getAccSepa->execute($existingAccount, $this->pinFile);
    }

    protected function getStoragePath()
    {
        return __DIR__ . '/../storage';
    }


    /**
     * Generate a unique ID per transaction
     * We assume, that all transactions of a day are always present in the fetched transactions
     * (after a while, very old transactions might not appear)
     * So we generate a continues key per day, combine it with the date and have a globally unique key
     *
     * @param Transaction[] $transactions
     * @return Transaction[]
     */
    public function keyTransactions(array $transactions)
    {
        $result = [];
        $dateCounters = array();
        foreach ($transactions as $transaction) {
            $date = $transaction->getDate()->format('Ymd');
            if ( ! isset($dateCounters[$date])) {
                $dateCounters[$date] = 0;
            }
            $dateCounters[$date]++;
            $uniqId = self::formatKey($date, $dateCounters[$date]);
            $result[$uniqId] = $transaction;
        }

        return $result;
    }

    public static function formatKey(string $date, int $increment)
    {
        return $date . sprintf('%05d', $increment);
    }

    public function getFinalBalance($balances)
    {
        usort($balances, function($a, $b) {
            return - ($a->getDate() <=> $b->getDate());
        });

        return $balances[0]->getValue()->getAmount() / 100;
    }

}
