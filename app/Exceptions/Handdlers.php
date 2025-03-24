<?php

namespace App\Exceptions;

use Exception;
use Inertia\Inertia;

class Handdlers extends Exception
{
    public function render($request, \Throwable $e)
    {
        $response = parent::render($request, $e);
        $status = $response->status();

        if (app()->environment(['local', 'testing'])) {

            return match ($status) {
                404 => Inertia::render('Errors/404')->toResponse($request)->setStatusCode($status),
                500, 503 => Inertia::render('Errors/500')->toResponse($request)->setStatusCode($status),
                403 => Inertia::render('Errors/403')->toResponse($request)->setStatusCode($status),
                401 => Inertia::render('Errors/401')->toResponse($request)->setStatusCode($status),
                419 => redirect()->back()->withErrors(['status' => __('The page expired, please try again.')]),
                default => $response
            };

        }
        return $response;
    }

}
