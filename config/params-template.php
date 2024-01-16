<?php

return [
    'jupyter_deployments_url' => 'jupyter delpoyment url',
    'jupyter_services_url' => 'jupyter service url',
    'jupyter_ingresses_url' => 'jupyter ingress url',
    'jupyter_bearer_token' => 'jupyter auth token',
    'tmpFolderPath' => 'json folder for jupyter',
    'userDataPath' => '',
    'systemUser' => '',
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


    /*
    minimumUsernameLength

    Set this value to the minimum length of a user's username, as per deployment policies

    This value is used to determine the minimum input text length in autocomplete fields where users are being searched
    based on their username. To avoid missing users whose usernames are smaller than the autocomplete value, this
    parameter is inspected.

    For example, if a user can have username of user0 but the input text length must be at least 6 characters before
    the request is made, then no matter what input text is given, user0 will not be retrievable.

    If this value is missing from the parameters, then autocomplete fields will issue requests even for 1-character
    inputs, which is also a safe default. This however, gets inefficient quickly with the increase of the user-base.
    Thus, consider defining the minimum username length used throughout the deployment.
    */
    'minUsernameLength' => 1,
    'email_verification' => [
        'validity_period' => '1 day', // Value should be a string literal that can be added on a date('c') object
        'email_verification_url' => 'url to the email verification view'
    ]

];
