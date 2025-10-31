<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProblemController extends Controller
{
    // Create new problem
    public function store(Request $request)
    {
        $validated = $request->validate([
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
            'created_by' => auth()->id(),
            'department' => $validated['department'],
            'priority' => $validated['priority'],
            'statement' => $validated['statement'],
            'image' => $imagePath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Problem submitted successfully',
            'data' => $problem->load('creator')
        ], 201);
    }

    // Get all problems
    public function index(Request $request)
    {
        $query = Problem::with(['creator', 'assignedUser', 'solver', 'verifier']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by department
        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // If team member, show their created problems or assigned problems
        if (auth()->user()->isTeamMember()) {
            $query->where(function($q) {
                $q->where('created_by', auth()->id())
                  ->orWhere('assigned_to', auth()->id());
            });
        }

        // If team leader, show all problems
        $problems = $query->orderBy('created_at', 'desc')->get();

        return response()->json($problems);
    }

    // Get single problem
    public function show($id)
    {
        $problem = Problem::with(['creator', 'assignedUser', 'solver', 'verifier'])->findOrFail($id);
        return response()->json($problem);
    }

    // Assign problem (Admin or Team Leader only)
    public function assign(Request $request, $id)
    {
        // Check if user is admin or team leader
        if (!auth()->user()->isAdmin() && !auth()->user()->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $problem = Problem::findOrFail($id);
        $problem->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        return response()->json([
            'message' => 'Problem assigned successfully',
            'data' => $problem->load(['creator', 'assignedUser'])
        ]);
    }

    // Reassign problem (Team Leader or assigned person can reassign)
    public function reassign(Request $request, $id)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'comment' => 'nullable|string',
        ]);

        $problem = Problem::findOrFail($id);

        // Check if user can reassign (team leader or currently assigned person)
        if (!auth()->user()->isTeamLeader() && $problem->assigned_to !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $problem->update([
            'assigned_to' => $validated['assigned_to'],
            'comment' => $validated['comment'] ?? $problem->comment,
        ]);

        return response()->json([
            'message' => 'Problem reassigned successfully',
            'data' => $problem->load(['creator', 'assignedUser'])
        ]);
    }

    // Update problem status
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,solved,verified,closed',
            'comment' => 'nullable|string',
        ]);

        $problem = Problem::findOrFail($id);
        $problem->update([
            'status' => $validated['status'],
            'comment' => $validated['comment'] ?? $problem->comment,
        ]);

        if ($validated['status'] === 'in_progress') {
            $problem->update(['solved_by' => auth()->id()]);
        }

        return response()->json([
            'message' => 'Problem status updated',
            'data' => $problem->load(['creator', 'assignedUser', 'solver'])
        ]);
    }

    // Submit solution
    public function submitSolution(Request $request, $id)
    {
        $validated = $request->validate([
            'solution' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        $problem = Problem::findOrFail($id);

        // Check if user is assigned to this problem
        if ($problem->assigned_to !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $problem->update([
            'solution' => $validated['solution'],
            'comment' => $validated['comment'] ?? $problem->comment,
            'status' => 'solved',
            'solved_by' => auth()->id(),
            'solved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Solution submitted successfully. Waiting for verification.',
            'data' => $problem->load(['creator', 'assignedUser', 'solver'])
        ]);
    }

    // Verify solution (by problem creator)
    public function verifySolution(Request $request, $id)
    {
        $validated = $request->validate([
            'is_verified' => 'required|boolean',
            'comment' => 'nullable|string',
        ]);

        $problem = Problem::findOrFail($id);

        // Check if user is the creator
        if ($problem->created_by !== auth()->id()) {
            return response()->json(['message' => 'Only problem creator can verify solution'], 403);
        }

        if ($validated['is_verified']) {
            $problem->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'comment' => $validated['comment'] ?? $problem->comment,
            ]);

            return response()->json([
                'message' => 'Solution verified successfully',
                'data' => $problem->load(['creator', 'assignedUser', 'solver', 'verifier'])
            ]);
        } else {
            // If not verified, send back to assigned person
            $problem->update([
                'status' => 'assigned',
                'comment' => $validated['comment'] ?? 'Solution needs revision',
            ]);

            return response()->json([
                'message' => 'Solution rejected. Problem sent back for revision.',
                'data' => $problem->load(['creator', 'assignedUser', 'solver'])
            ]);
        }
    }

    // Close problem (Admin or Team Leader only)
    public function close(Request $request, $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'comment' => 'nullable|string',
        ]);

        $problem = Problem::findOrFail($id);
        $problem->update([
            'status' => 'closed',
            'comment' => $validated['comment'] ?? $problem->comment,
        ]);

        return response()->json([
            'message' => 'Problem closed successfully',
            'data' => $problem
        ]);
    }

    // Delete problem (Admin only)
    public function destroy($id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $problem = Problem::findOrFail($id);
        
        // Delete image if exists
        if ($problem->image) {
            Storage::disk('public')->delete($problem->image);
        }

        $problem->delete();

        return response()->json(['message' => 'Problem deleted successfully']);
    }
}