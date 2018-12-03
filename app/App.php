<?php

namespace Mestrona\Bank;



class App
{
    protected $accounts;

    public function __construct(array $accounts)
    {
        $this->accounts = $accounts;
    }

    public function run()
    {
        $account = new Account($this->accounts['default']);
        $account->fetch();
    }
}