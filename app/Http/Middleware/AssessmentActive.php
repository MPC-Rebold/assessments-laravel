<?php

namespace App\Http\Middleware;

use App\Models\AssessmentCourse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssessmentActive
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

        $assessmentId = $request->route('assessmentId');
        $courseId = $request->route('courseId');

        $assessmentCourse = AssessmentCourse::where('assessment_canvas_id', $assessmentId)
            ->where('course_id', $courseId)
            ->first();

        if (! $assessmentCourse) {
            abort(404);
        }

        if (! $assessmentCourse->is_active) {
            abort(403);
        }

        return $next($request);
    }
}
