<?php

namespace App\Exceptions;

class BusinessException extends ApiException
{
    public function __construct(string $message, int $code = 400, mixed $errors = null)
    {
        parent::__construct($message, $code, $errors);
    }
}
