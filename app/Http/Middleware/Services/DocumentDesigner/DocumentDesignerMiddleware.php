<?php

namespace App\Http\Middleware\Services\DocumentDesigner;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DocumentDesignerMiddleware
{
    public function __construct()
    {
        //
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Gate::allows('document_designer'))abort(403);

        return $next($request);
    }
}
