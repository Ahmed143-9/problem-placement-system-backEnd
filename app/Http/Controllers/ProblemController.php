<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problem;
use Illuminate\Support\Facades\Storage;

class ProblemController extends Controller
{
    // Store new problem
    public function store(Request $request)
    {
        $request->validate([
            'department' => 'required|string',
            'priority' => 'required|string',
            'statement' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('problem_images', 'public');
        }

        $problem = Problem::create([
            'department' => $request->department,
            'priority' => $request->priority,
            'statement' => $request->statement,
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Problem submitted successfully',
            'data' => $problem
        ], 201);
    }

    // Show all problems
    public function index()
    {
        return Problem::latest()->get();
    }

    // Update problem (mark as solved)
    public function update(Request $request, $id)
    {
        $problem = Problem::findOrFail($id);

        $problem->update([
            'status' => $request->status ?? 'Solved',
            'comment' => $request->comment,
            'assigned_to' => $request->assigned_to,
        ]);

        return response()->json(['message' => 'Problem updated successfully']);
    }
}
