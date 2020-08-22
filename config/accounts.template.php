<?php

$defaultBank = [
    // Enter your BIC here
    'bankCode' => '12345678',
    // Enter your HBCI URL
    'bankUrl' => 'https://....',
    // // 300 = FIN TS 3, might stay like this
    'hbciVersion' => '300',
];

$defaultDbConnection = [
    'pdoConnectionString' => 'mysql:host=localhost;dbname=mbank',
    'pdoUser' => 'foo',
    'pdoPassword' => 'bar',
    'table' => 'mbank_transactions',
    'primaryKey' => 'acc_trans',
];

$hooks = [
    'process_row' => function($row) {
        return $row;
    },
    'after_saved' => function($rows) {

    }
];

return [
    'default' => [
        // Assign the bank here
        'bank' => $defaultBank,
        // Assign the database connection here
        'database' => $defaultDbConnection,
        // Your acounts user ID
        'userId' => 'example',
        // Your account Owner (your name)
        'accountOwner' => 'Max Mustermann',
        // Account Number
        'accountNumber' => '123456',
        // Your PIN
        'pin' => 'SuperSecret',
        // TAN mode, use "listtans" command manually to check the available modes
        'tanMode' => 6921,
        // Define Hooks
        'hooks' => $hooks,
    ],
];