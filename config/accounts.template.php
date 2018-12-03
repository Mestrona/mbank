<?php

$defaultBank = [
    // Enter your BIC here
    'bankCode' => '12345678',
    // Enter your HBCI URL
    'bankUrl' => 'https://....',
    // // 300 = FIN TS 3, might stay like this
    'hbciVersion' => '300',
];

return [
    'default' => [
        // Assign the bank here
        'bank' => $defaultBank,
        // Your acounts user ID
        'userId' => 'example',
        // Your account Owner (your name)
        'accountOwner' => 'Max Mustermann',
        // Account Number
        'accountNumber' => '123456',
        // Your PIN
        'pin' => 'SuperSecret',
    ],
];