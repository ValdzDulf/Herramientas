<?php

/*
|--------------------------------------------------------------------------
| Holds configuration settings for the connection to the database.
|--------------------------------------------------------------------------
|
| Defining the filed options.
|
| array[]                  array  Defines the different instances of connection to the database.
|     array[<profileName>] array  Defines the connection parameters of the instance.
|         ['driver']       string Name of the driver used to make the connection.
|         ['host']         string Database server location.
|         ['database']     string Database name.
|         ['username']     string User's name to connect to the database.
|         ['password']     string User's password to connect to database.
|
*/

return [
        '<profileName>' => [
                'driver' => 'mysqli',
                'host' => '<host>',
                'database' => '<database>',
                'username' => '<username>',
                'password' => '<password>'
        ]
];
