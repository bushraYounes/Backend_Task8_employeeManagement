<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentStoreRequest;
use App\Http\Requests\DepartmentUpdateRequest;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        // in this api we return all departments and all there employees 
        try {
            $departments = Department::with('employees')->get();

        return response()->json([
            'status' => 'success',
            'departments' => $departments,
        ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => 'failed',
            ]);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $department = Department::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            DB::commit();
            return response()->json([
                'statuse' => 'Store Success',
                'department' => $department
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);

            return response()->json([
                'statuse' => 'Store Failed',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        // in this api we return specific department and all its employees 
        return response()->json([
            'status' => 'success',
            'department' => $department::with('employees')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentUpdateRequest $request, Department $department)
    {
        try {
            DB::beginTransaction();
            $newData = [];

            if (isset($request->title)) {
                $newData['name'] = $request->name;
            }
            if (isset($request->subtitle)) {
                $newData['description'] = $request->description;
            }
           
            $department->update($newData);

            

            DB::commit();
            return response()->json([
                'statuse' => 'Update Success',
                'department' => $department,
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
    public function destroy(Department $department)
    {
        try {
            DB::beginTransaction();
            
            $department->notes()->delete();

            Employee::where('department_id', $department->id)->delete();
          
            $department->delete(); 
            DB::commit();
            return response()->json([
                'status' => 'Delete Department and all its Employees Successfully',
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            return response()->json([
                'status' => 'Delete Failed',
            ]);
        }
    }

    public function forceDelete(Department $department)
    {
        try {
            DB::beginTransaction();
            $department->forceDelete();

            DB::commit();

            return response()->json([
                'status' => 'Department permanently deleted',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);

            return response()->json([
                'status' => 'Failed to permanently delete department',
            ], 500);
        }
    }
}
