<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoodleCourse;

class MoodleUserController extends Controller
{
    public function enrolledCourses(Request $request)
    {
        $username = $request->input('username');

        $courses = MoodleCourse::query()
            ->select([
                'mdl_user.id as userid',
                'mdl_course.id as courseid',
                'mdl_course.fullname',
                'mdl_user.currentlogin'
            ])
            ->join('mdl_enrol', 'mdl_enrol.courseid', '=', 'mdl_course.id')
            ->join('mdl_user_enrolments', 'mdl_user_enrolments.enrolid', '=', 'mdl_enrol.id')
            ->join('mdl_user', 'mdl_user.id', '=', 'mdl_user_enrolments.userid')
            ->where('mdl_user.suspended', 0)
            ->where('mdl_user.deleted', 0)
            ->where('mdl_user_enrolments.status', 0)
            ->where('mdl_course.category', 265)
            ->where('mdl_user.username', $username)
            ->orderBy('mdl_course.fullname')
            ->distinct()
            ->get();

        return response()->json($courses);
    }
}
