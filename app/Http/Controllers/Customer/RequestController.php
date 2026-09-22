<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Request as ResidentRequest;
use App\Models\User;
use App\Notifications\NewResidentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class RequestController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $requests = $customer->requests()->latest()->paginate(15);

        return view('customer.requests.index', compact('requests'));
    }

    public function create()
    {
        return view('customer.requests.create', ['types' => ResidentRequest::TYPES]);
    }

    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $noticeDays = (int) setting('notice_period_days', 30);
        $earliestMoveOut = now()->addDays($noticeDays)->toDateString();

        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(ResidentRequest::TYPES)),
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'preferred_date' => array_filter([
                $request->input('type') === 'vacation' ? 'required' : 'nullable',
                'date',
                $request->input('type') === 'vacation' ? "after_or_equal:{$earliestMoveOut}" : null,
            ]),
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'preferred_date.after_or_equal' => "Vacation notices need at least {$noticeDays} days' notice — the earliest move-out date is {$earliestMoveOut}.",
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('requests/attachments', 'public');
        }
        unset($validated['attachment']);

        $residentRequest = $customer->requests()->create($validated + ['status' => 'pending']);

        try {
            Notification::send(User::query()->where('is_active', true)->get(), new NewResidentRequest($residentRequest));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('customer.requests.index')->with('success', 'Your request has been submitted. The office will get back to you soon.');
    }

    public function show(ResidentRequest $request)
    {
        abort_unless($request->customer_id === Auth::guard('customer')->id(), 404);

        return view('customer.requests.show', ['residentRequest' => $request]);
    }
}
