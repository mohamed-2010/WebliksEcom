<?php
namespace App\Traits;

use App\Enum\ResponseCodeEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

trait ApiResponseTrait
{
    /**
     * Retrieve the current application language.
     *
     * @return string
     */
    protected function currentLanguage(): string
    {
        return App::getLocale();
    }

    /**
     * Return a structured error response.
     *
     * @param string $message Error message.
     * @param int $statusCode HTTP status code.
     * @param array|null $errors Optional additional error details.
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400, array $errors = null): JsonResponse
    {
        $response = [
            'status' => false,
            'code'   => $statusCode,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a structured success response.
     *
     * @param string $message Success message.
     * @param int $statusCode HTTP status code.
     * @param array|null $data Optional additional data.
     * @return JsonResponse
     */
    protected function successResponse(string $message, int $statusCode = 200, array $data = null): JsonResponse
    {
        $response = [
            'status' => true,
            'code'   => $statusCode,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a data-only response.
     *
     * @param array $data The data to return.
     * @param int $statusCode HTTP status code.
     * @param string|null $message Optional success message.
     * @return JsonResponse
     */
    protected function dataResponse(array|object $data, int $statusCode = 200, string $message = null): JsonResponse
    {
        return response()->json([
            'status' => true,
            'code'   => $statusCode,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Return a response with localized message.
     *
     * @param string $translationKey The translation key.
     * @param bool $isSuccess Indicates if the response is success or error.
     * @param int $statusCode HTTP status code.
     * @param array|null $extra Optional additional data or errors.
     * @return JsonResponse
     */
    protected function localizedResponse(string $translationKey, bool $isSuccess, int $statusCode, array $extra = null): JsonResponse
    {
        $message = __($translationKey);

        if ($isSuccess) {
            return $this->successResponse($message, $statusCode, $extra);
        }

        return $this->errorResponse($message, $statusCode, $extra);
    }
}
