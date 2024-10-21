<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=clima',
    'username' => 'clima',
    'password' => 'your-password',
    'charset' => 'utf8',
    'on afterOpen' => function($event) {
        $event->sender->createCommand("SET datestyle = 'ISO, MDY'")->execute();
    }

    // Schema cache options (for production environment)
   // 'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
