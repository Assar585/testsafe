<?php

namespace App\Exceptions;

use App\Utility\NgeniusUtility;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

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
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        // TEMP DEBUG - remove after fixing
        echo "<pre style='font-family:monospace;background:#fff;color:#333;padding:20px;text-align:left'>";
        echo "<b>Error:</b> " . htmlspecialchars($e->getMessage()) . "\n";
        echo "<b>File:</b> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
        exit;

        if ($e instanceof Redirectingexception) {
            return redirect()->back();
        }

        if ($this->isHttpException($e)) {
            if ($request->is('customer-products/admin')) {
                return NgeniusUtility::initPayment();
            }

            return parent::render($request, $e);
        } else {
            return parent::render($request, $e);
        }
    }
}