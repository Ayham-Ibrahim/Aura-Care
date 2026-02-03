<?php

namespace App\Exceptions;

class ValidationException extends ApiException
{
    public function __construct(mixed $errors, string $message = 'خطأ في البيانات المدخلة')
    {
        parent::__construct($message, 422, $errors);
    }
}
