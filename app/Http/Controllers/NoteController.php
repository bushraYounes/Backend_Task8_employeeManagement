<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\NoteStoreRequest;
use App\Http\Requests\NoteUpdateRequest;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $notes = Note::all();


            return response()->json([
                'status' => 'success',
                'notes' => $notes,
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
    public function store(NoteStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $note = Note::create([
                'message' => $request->message,
                'notable_type' => $request->notable_type,
                'notable_id' => (int)$request->notable_id,
            ]);

            if ($note->notable_type == 'department') {

                $department = Department::where('id', $request->notable_id)->first();
                if ($department) {
                    $department->notes()->create([
                        'note' => $request->message,
                    ]);
                } else {
                    return response()->json([
                        'error' => 'No Such Department',

                    ]);
                }
            } else if ($note->notable_type == 'employee') {
                $employee = Employee::where('id', $request->notable_id)->first();
                if ($employee) {
                    $employee->notes()->create([
                        'note' => $request->message,
                    ]);
                } else {
                    return response()->json([
                        'error' => 'No Such Department',

                    ]);
                }
            } else {
                return response()->json([
                    'error' => 'note should be added to department or employee only',

                ]);
            }
            DB::commit();
            return response()->json([
                'statuse' => 'Store Success',
                'note' => $note
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
    public function show(Note $note)
    {
        return response()->json([
            'status' => 'success',
            'note' => $note
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NoteUpdateRequest $request, Note $note)
    {
        try {
            DB::beginTransaction();
            $newData = [];

            if (isset($request->message)) {
                $newData['message'] = $request->message;
            }

            $note->update($newData);
            DB::commit();
            return response()->json([
                'statuse' => 'Update Success',
                'note' => $note,
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
    public function destroy(Note $note)
    {
        try {

           
            $note->delete();
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
