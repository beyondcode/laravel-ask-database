<?php

return [
    /**
     * The database connection name to use. Depending on your
     * use case, you might want to limit the database user
     * to have read-only access to the database.
     */
    'connection' => env('ASK_DATABASE_DB_CONNECTION', 'mysql'),

    /**
     * Strict mode will throw an exception when the query
     * would perform a write/alter operation on the database.
     *
     * If you want to allow write operations - or if you are using a read-only
     * database user - you may disable strict mode.
     */
    'strict_mode' => env('ASK_DATABASE_STRICT_MODE', true),
];
