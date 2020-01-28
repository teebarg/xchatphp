<?php

namespace App\Exceptions;

//use App\Helpers\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseCodes;
use App\Helpers\ResponseHelper;
use App\Helpers\ResponseMessages;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        CustomException::class,

    ];

    /**
     *
     * @var AuthService
     */
//    protected $authService;


    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param Exception $exception
     * @return Response
     */
    public function render($request, Exception $e) {
        if ($e instanceof CustomException) {
            return ResponseHelper::createErrorResponse($e->getMessage(), $e->getCode(), [
                'errors' => $e->getErrorMessages()
            ]);
        } elseif ($e instanceof ValidationException) {//handle validation errors
            $data = ["errors" => $e->validator->getMessageBag()->getMessages()];
            return ResponseHelper::createErrorResponse(ResponseMessages::FAILED_VALIDATION, ResponseCodes::FAILED_VALIDATION, $data, ResponseCodes::UNPROCESSABLE_ENTITY);
        } elseif ($e instanceof ModelNotFoundException) {
            return ResponseHelper::createErrorResponse(
                ResponseMessages::RESOURCE_NOT_FOUND, ResponseCodes::RESOURCE_NOT_FOUND
            );
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            return ResponseHelper::createErrorResponse(
                ResponseMessages::ROUTE_NOT_FOUND, ResponseCodes::ROUTE_NOT_FOUND, [], 404
            );
        } elseif ($e instanceof DevException) {
            $data = ['contextData' => $e->getContextData()];
            return ResponseHelper::createErrorResponse($e->getUserMessage(), $e->getCode(), $data);
        } elseif ($e instanceof AuthenticationException) {
            return ResponseHelper::createErrorResponse($e->getMessage(), ResponseCodes::RESOURCE_AUTHORISATION_ERROR, [], ResponseCodes::UNAUTHENTICATED);
        }elseif ($e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return ResponseHelper::createErrorResponse(ResponseMessages::PERMISSION_DENIED, ResponseCodes::PERMISSION_DENIED, [], ResponseCodes::PERMISSION_DENIED);
        } else {
//            dd($e);
            return ResponseHelper::createErrorResponse(
                ResponseMessages::EXCEPTION_THROWN, ResponseCodes::EXCEPTION_THROWN,
                [
                    "error_message" => $e->getMessage(),
                    "error" => in_array(env('APP_ENV'), ['testing', 'staging', 'local']) ? $e->getTrace() : []
                ]
            );
        }
    }

    protected function getLoggedInUser() {
        return Auth::check() ?
            Auth::user() : null;
    }

    protected function createRequestData(Request $request) {
        return [
            "http_method" => $request->getMethod(),
            "query_data" => $request->query->all(),
            "body_data" => $request->request->all(),
            "request_uri" => $request->getRequestUri()
        ];
    }

    protected function extractExceptionData(Exception $e) {
        return ['exception_message' => $e->getMessage(),
            'exception_trace' => array_slice($e->getTrace(), 0, 10)];
    }


}
