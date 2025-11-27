<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Return 204 for transient errors on Livewire requests so the UI isn't replaced by an error overlay.
     */
    public function render($request, Throwable $e): Response
    {
        if ($this->isLivewireRequest($request)) {
            $status = $this->statusFrom($e);

            if (in_array($status, [408, 429, 500, 502, 503, 504], true)) {
                return response()->noContent(204);
            }
        }

        return parent::render($request, $e);
    }

    protected function isLivewireRequest(Request $request): bool
    {
        // Livewire v3 sets the X-Livewire header on its requests
        return $request->headers->has('X-Livewire');
    }

    protected function statusFrom(Throwable $e): int
    {
        return $e instanceof HttpExceptionInterface
            ? $e->getStatusCode()
            : 500;
    }
}