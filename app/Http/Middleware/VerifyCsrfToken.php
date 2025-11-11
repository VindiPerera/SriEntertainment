<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/api/products',
        '/api/refill-laminating',
        '/api/refill-laminating-by-code',
        '/api/laminating-services',
        '/api/refill-printout',
        '/api/refill-binding',
        '/api/refill-photocopy',
    ];
}
