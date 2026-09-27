<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\IntakeApplication;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * A hostel's public, no-login self-registration link (see the "register"
 * route group in routes/web.php) — a prospective resident fills their own
 * details before an admin ever sees them, saving the admin's typing at
 * check-in time. Nothing here creates a Customer or Booking directly; an
 * admin reviews and converts each application from Admin\IntakeApplicationController.
 */
class IntakeController extends Controller
{
    public function create(Tenant $tenant)
    {
        abort_unless($tenant->isActive(), 404);

        app()->instance('currentTenantId', $tenant->id);

        $branches = Branch::orderBy('name')->get();

        return view('public.intake.create', compact('tenant', 'branches'));
    }

    public function store(Request $request, Tenant $tenant)
    {
        abort_unless($tenant->isActive(), 404);

        app()->instance('currentTenantId', $tenant->id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'dob' => 'required|date|before:today',
            'address' => 'required|string',
            'guardian_phone' => 'required|string|max:20',
            'work_details' => 'nullable|string',
            // Plain exists:branches,id would run a raw query-builder check that bypasses
            // Branch's tenant scope, letting a submission reference another tenant's branch.
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('tenant_id', $tenant->id)],
            'preferred_move_in_date' => 'nullable|date',
            'photo' => 'nullable|image|max:10240',
            'id_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $photoPath = null;
        $idProofPath = null;

        try {
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('intake/photos', 'public');
            }
            if ($request->hasFile('id_proof')) {
                $idProofPath = $request->file('id_proof')->store('intake/proofs', 'public');
            }
        } catch (\Exception $e) {
            \Log::warning('Intake application file upload failed', ['tenant_id' => $tenant->id, 'error' => $e->getMessage()]);
        }

        IntakeApplication::create($validated + [
            'photo_path' => $photoPath,
            'id_proof_path' => $idProofPath,
        ]);

        return view('public.intake.submitted', compact('tenant'));
    }
}
