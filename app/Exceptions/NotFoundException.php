<?php

namespace App\Exceptions;

class NotFoundException extends ApiException
{
    public function __construct(string $message = 'العنصر المطلوب غير موجود')
    {
        parent::__construct($message, 404);
    }
}
