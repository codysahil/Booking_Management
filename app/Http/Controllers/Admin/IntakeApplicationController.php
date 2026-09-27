<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntakeApplication;
use App\Models\Tenant;
use Illuminate\Http\Request;

class IntakeApplicationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', IntakeApplication::STATUS_PENDING);

        $applications = IntakeApplication::with('branch')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $tenant = Tenant::find(auth()->user()->tenant_id);
        $intakeLink = $tenant ? route('register.create', $tenant) : null;

        return view('admin.intake-applications.index', compact('applications', 'status', 'intakeLink'));
    }

    public function show(IntakeApplication $intakeApplication)
    {
        $intakeApplication->load('branch', 'customer');

        return view('admin.intake-applications.show', compact('intakeApplication'));
    }

    public function reject(Request $request, IntakeApplication $intakeApplication)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $intakeApplication->update([
            'status' => IntakeApplication::STATUS_REJECTED,
            'admin_notes' => $validated['admin_notes'] ?? $intakeApplication->admin_notes,
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.intake-applications.index')->with('success', 'Application marked as rejected.');
    }
}
