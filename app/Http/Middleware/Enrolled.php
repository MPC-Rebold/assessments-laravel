<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Enrolled
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $courseId = $request->route('courseId');
        if (auth()->user()->isEnrolled($courseId)) {
            return $next($request);
        }

        return redirect('dashboard');
    }
}
