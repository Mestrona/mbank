mBank
=====

Simple Transaction fetcher by Mestrona GbR.

Installation
============

1. Clone the git repository
2. Install composer (if not yet done)
3. run `composer install`

Usage
=====

1. Copy config/accounts.template.php to config/accounts.php and fill info.
2. Call `php mbank install default` to create the database table
2. Call `php mbank fetch default` to fetch the recent transactions for the default
account. You can configure as many accounts as you like


Debugging
=========

After initializing, try manually

    export BIC=
    export ACCOUNT=
    aqbanking-cli  --acceptvalidcerts request --bank=$BIC--account=$ACOUNT--ctxfile=../storage/aqBanking.ctx --balance --transactions
