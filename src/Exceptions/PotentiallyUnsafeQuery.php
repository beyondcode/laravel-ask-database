<?php

namespace BeyondCode\Oracle\Exceptions;

use Exception;

class PotentiallyUnsafeQuery extends Exception
{
    public static function fromQuery(string $query): self
    {
        return new self("The query `{$query}` is potentially unsafe.");
    }
}
