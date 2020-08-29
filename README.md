mBank
=====

Simple Transaction fetcher by Mestrona GbR.

Works with AqBanking to fetch bank transactions and save them to database table.


Installation
============

1. Clone the git repository
2. Install composer (if not yet done)
3. run `composer install`
4. Install the database schema from `schema.sql` and `schema_update_1.sql`

Usage
=====

1. Copy config/accounts.template.php to config/accounts.php and fill info
   (database info + account data)
   
2. Call `php mbank default` to fetch the recent transactions for the default
account. You can configure as many accounts as you like.
Call `php mbank --all` to fetch recent transaction for all configured accounts.


3. If the tan mode is wrong, you can find out the valid ones by calling `aqhbci-tool4 listitanmodes --user=1`

Debugging
=========

After initializing, try manually

    export BIC=  # Enter bank code here
    export ACCOUNT=  # Enter account code here
    aqbanking-cli --acceptvalidcerts request --bank=$BIC --account=$ACCOUNT --ctxfile=.storage/aqBanking.ctx --balance --transactions
    aqbanking-cli --acceptvalidcerts getaccounts--bank=$BIC --account=$ACCOUNT
    aqbanking-cli --acceptvalidcerts listaccounts --bank=$BIC --account=$ACCOUNT 

License
=======

MIT