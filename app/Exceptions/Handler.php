<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use App\Support\Code;
use App\Support\Response;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

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
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception)
    {
        if (config('app.debug') && (boolean)$request->input('raw')) {
            return parent::render($request, $exception);
        }

        if ($exception instanceof QueryException) {
            Code::setCode(Code::ERR_QUERY);
        } elseif ($exception instanceof \PDOException) {
            Code::setCode(Code::ERR_DB);
        } elseif ($exception instanceof ValidationException) {
            Code::setDetail($exception->errors());
            Code::setCode(Code::ERR_PARAMS, null, array_values($exception->errors())[0]);
        } elseif ($exception instanceof ModelNotFoundException) {
            Code::setCode(Code::ERR_MODEL);
        } elseif ($exception instanceof AuthenticationException) {
            Code::setCode(Code::ERR_HTTP_UNAUTHORIZED);
        } elseif ($exception instanceof ApiException) {
            // 定义 ApiException 时已经做过了处理
        } else {
            $res = parent::render($request, $exception);
            $code = $res->getStatusCode();
            Code::setCode($code);
        }

        $response = new Response();
        $response->setException($exception);

        return $response->send();
    }
}
