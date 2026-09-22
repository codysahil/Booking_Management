<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request as ResidentRequest;
use App\Notifications\RequestStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ResidentRequest::with('customer');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        return view('admin.requests.index', compact('requests'));
    }

    public function show(ResidentRequest $request)
    {
        $request->load('customer.bookings.bed.room.branch', 'handler');

        return view('admin.requests.show', ['residentRequest' => $request]);
    }

    public function update(Request $httpRequest, ResidentRequest $request)
    {
        $validated = $httpRequest->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(\App\Models\Request::STATUSES)),
            'admin_response' => 'nullable|string',
        ]);

        $request->update($validated + [
            'handled_by' => Auth::id(),
            'resolved_at' => in_array($validated['status'], ['approved', 'rejected', 'completed']) ? now() : null,
        ]);

        try {
            Notification::send($request->customer, new RequestStatusChanged($request));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Request updated and the resident has been notified.');
    }
}
