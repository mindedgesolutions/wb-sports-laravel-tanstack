<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CompTrainCourseDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ComputerTraining extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = CompTrainCourseDetail::where('organisation', "services")
            ->searchCourse($search)
            ->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total(),
            ]
        ], Response::HTTP_OK);
    }

    // --------------------------------

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'courseType' => 'required|string',
            'courseName' => 'required|string',
            'duration' => 'required|string',
            'eligibility' => 'required|string',
            'courseFee' => 'required|string',
        ], [
            '*.required' => ':Attribute is required',
        ], [
            'courseType' => 'Course type',
            'courseName' => 'Course name',
            'duration' => 'Course duration',
            'eligibility' => 'Course eligibility',
            'courseFee' => 'Course fees',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $slug = Str::slug($request->input('courseName'));
            $check = CompTrainCourseDetail::where('course_slug', $slug)->first();
            if ($check) {
                return response()->json(['errors' => ['Course already exists']], Response::HTTP_CONFLICT);
            }

            CompTrainCourseDetail::create([
                'course_type' => $request->input('courseType'),
                'course_name' => trim($request->input('courseName')),
                'course_slug' => Str::slug($request->input('courseName')),
                'course_duration' => $request->input('duration'),
                'course_eligibility' => trim($request->input('eligibility')),
                'course_fees' => $request->input('courseFee'),
                'organisation' => 'services',
            ]);

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // --------------------------------

    public function toggle(Request $request, string $id)
    {
        CompTrainCourseDetail::where('id', $id)->update(['is_active' => $request->checked]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // --------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'courseType' => 'required|string',
            'courseName' => 'required|string',
            'duration' => 'required|string',
            'eligibility' => 'required|string',
            'courseFee' => 'required|string',
        ], [
            '*.required' => ':Attribute is required',
        ], [
            'courseType' => 'Course type',
            'courseName' => 'Course name',
            'duration' => 'Course duration',
            'eligibility' => 'Course eligibility',
            'courseFee' => 'Course fees',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $slug = Str::slug($request->input('courseName'));
            $check = CompTrainCourseDetail::where('course_slug', $slug)->where('id', '!=', $id)->first();
            if ($check) {
                return response()->json(['errors' => ['Course already exists']], Response::HTTP_CONFLICT);
            }

            CompTrainCourseDetail::where('id', $id)->update([
                'course_type' => $request->input('courseType'),
                'course_name' => trim($request->input('courseName')),
                'course_slug' => Str::slug($request->input('courseName')),
                'course_duration' => $request->input('duration'),
                'course_eligibility' => trim($request->input('eligibility')),
                'course_fees' => $request->input('courseFee'),

            ]);

            DB::commit();

            return response()->json(['message' => 'success'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        CompTrainCourseDetail::where('id', $id)->delete();
        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }


    public function courseList() // <-- Add Request here
    {
        try {

            $courses = CompTrainCourseDetail::where('organisation', "services")->where('is_active', true)->get();

            return response()->json(['courses' => $courses], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['message' => 'Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
