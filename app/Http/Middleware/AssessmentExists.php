<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssessmentExists
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->route('assessmentId') == -1) {
            return redirect()->route('course.show', ['courseId' => $request->route('courseId')]);
        }

        return $next($request);
    }
}
