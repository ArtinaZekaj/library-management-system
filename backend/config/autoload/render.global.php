<?php

if (getenv('RENDER') !== 'true') {
    return [];
}

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => \Doctrine\DBAL\Driver\PDO\PgSQL\Driver::class,
                'params' => [
                    'host' => getenv('DB_HOST'),
                    'port' => 5432,
                    'user' => getenv('DB_USER'),
                    'password' => getenv('DB_PASSWORD'),
                    'dbname' => getenv('DB_NAME'),
                ],
            ],
        ],
    ],
];