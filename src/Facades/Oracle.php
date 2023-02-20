<?php

namespace BeyondCode\Oracle\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BeyondCode\Oracle\Oracle
 */
class Oracle extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \BeyondCode\Oracle\Oracle::class;
    }
}
