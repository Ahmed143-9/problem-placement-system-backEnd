<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Problem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Get all pending users (waiting for approval)
    public function getPendingUsers()
    {
        $users = User::where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }

    // Approve user
    public function approveUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'role' => 'required|in:admin,team_leader,team_member',
        ]);

        $user->update([
            'is_approved' => true,
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'User approved successfully',
            'user' => $user
        ]);
    }

    // Reject user
    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User registration rejected and deleted'
        ]);
    }

    // Get all users
    public function getAllUsers()
    {
        $users = User::where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }

    // Update user role
    public function updateUserRole(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,team_leader,team_member',
        ]);

        $user = User::findOrFail($id);
        $user->update(['role' => $validated['role']]);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user
        ]);
    }

    // Deactivate/Activate user
    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'message' => $user->is_active ? 'User activated' : 'User deactivated',
            'user' => $user
        ]);
    }

    // Get dashboard statistics
    public function getDashboardStats()
    {
        $stats = [
            'total_users' => User::where('is_approved', true)->count(),
            'pending_users' => User::where('is_approved', false)->count(),
            'total_problems' => Problem::count(),
            'pending_problems' => Problem::where('status', 'pending')->count(),
            'in_progress_problems' => Problem::where('status', 'in_progress')->count(),
            'solved_problems' => Problem::where('status', 'solved')->count(),
            'verified_problems' => Problem::where('status', 'verified')->count(),
            'closed_problems' => Problem::where('status', 'closed')->count(),
            
            // Department wise problems
            'problems_by_department' => Problem::select('department', DB::raw('count(*) as count'))
                ->groupBy('department')
                ->get(),
            
            // Priority wise problems
            'problems_by_priority' => Problem::select('priority', DB::raw('count(*) as count'))
                ->groupBy('priority')
                ->get(),
            
            // Status wise problems
            'problems_by_status' => Problem::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
        ];

        return response()->json($stats);
    }

    // Generate live report
    public function generateLiveReport()
    {
        $report = [
            'generated_at' => now(),
            'overview' => [
                'total_problems' => Problem::count(),
                'pending' => Problem::where('status', 'pending')->count(),
                'assigned' => Problem::where('status', 'assigned')->count(),
                'in_progress' => Problem::where('status', 'in_progress')->count(),
                'solved' => Problem::where('status', 'solved')->count(),
                'verified' => Problem::where('status', 'verified')->count(),
                'closed' => Problem::where('status', 'closed')->count(),
            ],
            'by_department' => Problem::select('department', 'status', DB::raw('count(*) as count'))
                ->groupBy('department', 'status')
                ->get()
                ->groupBy('department'),
            'by_priority' => Problem::select('priority', 'status', DB::raw('count(*) as count'))
                ->groupBy('priority', 'status')
                ->get()
                ->groupBy('priority'),
            'top_solvers' => User::withCount('solvedProblems')
                ->orderBy('solved_problems_count', 'desc')
                ->limit(10)
                ->get(),
            'recent_activities' => Problem::with(['creator', 'assignedUser', 'solver', 'verifier'])
                ->orderBy('updated_at', 'desc')
                ->limit(20)
                ->get(),
        ];

        return response()->json($report);
    }
}