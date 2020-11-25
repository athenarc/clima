<?php

return [
    'bsDependencyEnabled'=>false,
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'openstackAuth'=> 
    [
        "auth"=> 
        [
            "identity"=>
            [
                "methods"=>
                [
                    "application_credential"
                ],
            
                "application_credential"=>
                [
                    "id"=> "",
                    "secret"=> ""
                ],
            ]
        ]
    ],
    'openstackProjectID' => '',
    'openstackFloatNetID' => '',
    'windowsImageIDs' =>
    [
        '<id1>'=> '<name1>',
        '<id2>'=> '<name2>',
    ],
    'windowsKeysFolder' => '/data/windows_keys/',
    'aai_auth'=>true,
    'logo-header'=>'',
    'logo-footer'=>'',
    'funding-footer'=>'',
    'youtube_url' => '',
    'twitter_url' => '',
];
