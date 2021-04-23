<?php

return [
    'bsDependencyEnabled'=>false,
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'windowsImageIDs' =>
    [
        //Fill this if you have Windows OS VM images
        '<id1>'=> '<name1>',
        '<id2>'=> '<name2>',
    ],
    'windowsKeysFolder' => 'This is the folder that saves the pairs of public/private keys for Windows VMs',
    'logo-header'=>'Location of the branding logo for the header or leave empty for default',
    'logo-footer'=>'Location of the branding logo for the center of the footer or leave empty for none',
    'funding-footer'=>'Location funding logo for the footer or leave empty for none',
    'youtube_url' => 'Fill if you have a youtube channel',
    'twitter_url' => 'Fill if you have a twitter account',
    'copyright' => 'Fill if you want to add a copyright text', 
];
