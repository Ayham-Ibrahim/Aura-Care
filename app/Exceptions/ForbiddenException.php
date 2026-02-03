<?php

namespace App\Exceptions;

class ForbiddenException extends ApiException
{
    public function __construct(string $message = 'ليس لديك صلاحية لهذا الإجراء')
    {
        parent::__construct($message, 403);
    }
}
