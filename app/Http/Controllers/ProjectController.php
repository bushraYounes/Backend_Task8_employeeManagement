<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $projects = Project::with('employees')->get();

            return response()->json([
                'status' => 'success',
                'projects' => $projects,
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => 'failed',
            ],400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectStoreRequest $request)
    {
         try {
            DB::beginTransaction();
            $project = Project::create([
                'name' => $request->name,
            ]);

            $project->employees()->attach($request->employee_ids);

            DB::commit();
            return response()->json([
                'statuse' => 'Store Success',
                'projects' => $project
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);

            return response()->json([
                'statuse' => 'Store Failed',
                'error'=>$th->getMessage(),
                'req'=>$request
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
         return response()->json([
            'status' => 'success',
            'project' => $project
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectUpdateRequest $request, Project $project)
    {
        try {
            DB::beginTransaction();
            $newData = [];

            if (isset($request->name)) {
                $newData['name'] = $request->name;
            }

            $project->update($newData);

            if (isset($request->employee_ids)) {
                $project->employees()->sync($request->employee_ids);
            }

            DB::commit();
            return response()->json([
                'statuse' => 'Update Success',
                'project' => $project,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);

            return response()->json([
                'statuse' => 'Update Failed',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        try {
            
            $project->employees()->detach();
            $project->delete();
            return response()->json([
                'status' => 'Delete Success',
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => 'Delete Failed',

            ]);
        }
    }
}
