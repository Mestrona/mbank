<?php

namespace Mestrona\Bank;

use AqBanking\Account as AqAccount;
use AqBanking\Bank;
use AqBanking\BankCode;
use AqBanking\Command\AddUserCommand;
use AqBanking\Command\GetSysIDCommand;
use AqBanking\Command\RenderContextFileToXMLCommand;
use AqBanking\Command\RequestCommand;
use AqBanking\ContextFile;
use AqBanking\ContextXmlRenderer;
use AqBanking\HbciVersion;
use AqBanking\PinFile\PinFile;
use AqBanking\PinFile\PinFileCreator;
use AqBanking\User;

class Account
{
    protected $config;
    protected $bankCode;
    protected $hbciVersion;
    protected $bank;
    protected $account;
    protected $user;
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

    public function fetch()
    {
        $this->initializeAqBanking();

        $contextFile = new ContextFile($this->getStoragePath() . '/aqBanking.ctx');

        $runRequest = new RequestCommand($this->account, $contextFile, $this->pinFile);
        $runRequest->execute();

        $render = new RenderContextFileToXMLCommand();
        $dom = $render->execute($contextFile);
        $result = new ContextXmlRenderer($dom);

        return $result;
    }

    protected function initializeAqBanking()
    {
        try {
            $addUser = new AddUserCommand();
            $addUser->execute($this->user);

            $createPinFile = new PinFileCreator($this->getStoragePath());
            $createPinFile->createFile($this->config['pin'], $this->user);

            $getSysId = new GetSysIDCommand();
            $getSysId->execute($this->user, $this->pinFile);


        } catch (\AqBanking\Command\AddUserCommand\UserAlreadyExistsException $e) {
            echo 'AqBanking is already initialized. If you want to reinitialize, '
                . 'you might want to delete .aqbanking in your home directory';
        }
    }

    protected function getStoragePath()
    {
        return __DIR__ . '/../storage';
    }

}