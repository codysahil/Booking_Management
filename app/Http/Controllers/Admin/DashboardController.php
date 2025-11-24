<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'branches' => \App\Models\Branch::count(),
            'rooms' => \App\Models\Room::count(),
            'beds' => \App\Models\Bed::count(),
            'vacant_beds' => \App\Models\Bed::where('status', 'vacant')->count(),
            'customers' => \App\Models\Customer::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
