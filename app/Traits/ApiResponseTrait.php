<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Success response
     */
    protected function successResponse(mixed $data = null, string $message = 'تمت العملية بنجاح', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Created response
     */
    protected function createdResponse(mixed $data = null, string $message = 'تم الإنشاء بنجاح'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Error response
     */
    protected function errorResponse(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse(mixed $errors, string $message = 'خطأ في البيانات المدخلة'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $message = 'العنصر المطلوب غير موجود'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'غير مصرح لك بالوصول'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(string $message = 'ليس لديك صلاحية لهذا الإجراء'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Server error response
     */
    protected function serverErrorResponse(string $message = 'حدث خطأ في الخادم'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }
}
