<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** ផ្លូវសម្រាប់អតិថិជន (កន្ត្រក, កម្មង់, បញ្ជីចូលចិត្ត) — Admin ប្រើមិនបាន ព្រោះ Admin លក់តាមទំព័រការលក់ */
class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && ! auth()->user()->isAdmin()) {
            return $next($request);
        }

        return redirect()->route('books.index')->with('error', 'មុខងារនេះសម្រាប់អតិថិជនប៉ុណ្ណោះ។');
    }
}
