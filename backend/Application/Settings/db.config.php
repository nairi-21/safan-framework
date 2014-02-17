<?php
return array(
    'connections' => array(
        //Default connection
        'default' => array(
            'start' => false,
            'type' => 'pdo',
            'config' => array(
                'db_host' => 'localhost',
                'db_user' => 'root',
                'db_pass' => 'root',
                'db_name' => 'safan',
                'db_charset' => 'utf-8',
                'db_debug' => 'false',
            ),
        ),
        //Other connections
    ),
);	
