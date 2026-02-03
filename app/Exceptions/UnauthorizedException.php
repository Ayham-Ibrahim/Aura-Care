<?php

namespace App\Exceptions;

class UnauthorizedException extends ApiException
{
    public function __construct(string $message = 'غير مصرح لك بالوصول')
    {
        parent::__construct($message, 401);
    }
}
