<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $employees = Employee::with(['department', 'projects'])->get();

            return response()->json([
                'status' => 'success',
                'employees' => $employees,
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
    public function store(EmployeeStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $employee = Employee::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'position' => $request->position,
                'department_id' => (int)$request->department_id
            ]);

            $employee->projects()->attach($request->projects_ids);

            DB::commit();
            return response()->json([
                'statuse' => 'Store Success',
                'employee' => $employee
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
    public function show(Employee $employee)
    {
        return response()->json([
            'status' => 'success',
            'employee' => $employee,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeUpdateRequest $request, Employee $employee)
    {
        try {
            DB::beginTransaction();
            $newData = [];

            if (isset($request->first_name)) {
                $newData['first_name'] = $request->first_name;
            }
            if (isset($request->last_name)) {
                $newData['last_name'] = $request->last_name;
            }
            if (isset($request->email)) {
                $newData['email'] = $request->email;
            }
            if (isset($request->position)) {
                $newData['position'] = $request->position;
            }
            if (isset($request->department_id)) {
                $newData['department_id'] = $request->department_id;
            }
            

            $employee->update($newData);

            if (isset($request->project_ids)) {
                $employee->projects()->sync($request->project_ids);
            }
            
            DB::commit();

            return response()->json([
                'statuse' => 'Update Success',
                'employee' => $employee,
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
    public function destroy(Employee $employee)
    {
        try {
            DB::beginTransaction();
            $employee->notes()->delete();

            //Employee::where('department_id', $department->id)->delete();
            
            $employee->projects()->detach();
            $employee->delete(); 
            DB::commit();
            return response()->json([
                'status' => 'Delete Employee Successfully',
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            return response()->json([
                'status' => 'Delete Failed',
            ]);
        }
    }
    public function forceDelete(Employee $employee)
    {
        try {
            DB::beginTransaction();
            $employee->forceDelete();

            DB::commit();

            return response()->json([
                'status' => 'Employee permanently deleted',
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
