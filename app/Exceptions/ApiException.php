<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected mixed $errors;

    public function __construct(string $message = 'حدث خطأ', int $code = 400, mixed $errors = null)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors(): mixed
    {
        return $this->errors;
    }

    public function render(): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $this->getMessage(),
        ];

        if ($this->errors !== null) {
            $response['errors'] = $this->errors;
        }

        return response()->json($response, $this->getCode());
    }
}
