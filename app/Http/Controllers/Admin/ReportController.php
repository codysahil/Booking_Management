<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Room;
use App\Models\MonthlyCharge;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $branch_id = $request->get('branch_id');

        // Date range based on period
        switch ($period) {
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
                break;
            case 'quarter':
                $quarter = ceil($month / 3);
                $startDate = Carbon::create($year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
                $endDate = Carbon::create($year, $quarter * 3, 1)->endOfMonth();
                break;
            case 'year':
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
                break;
            default:
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
        }

        // Revenue Statistics
        $totalRevenue = $this->getRevenue($startDate, $endDate, $branch_id);
        $previousPeriodRevenue = $this->getRevenue(
            $startDate->copy()->subDays($startDate->diffInDays($endDate) + 1),
            $startDate->copy()->subDay(),
            $branch_id
        );
        $revenueGrowth = $previousPeriodRevenue > 0 
            ? round((($totalRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100, 1)
            : 0;

        // Monthly revenue chart data (last 12 months)
        $monthlyRevenue = $this->getMonthlyRevenue($branch_id);

        // Revenue by branch
        $revenueByBranch = $this->getRevenueByBranch($startDate, $endDate);

        // Occupancy rate
        $occupancyData = $this->getOccupancyData($branch_id);

        // Customer statistics
        $customerStats = $this->getCustomerStats($startDate, $endDate, $branch_id);

        // Payment statistics
        $paymentStats = $this->getPaymentStats($startDate, $endDate, $branch_id);

        // Pending dues
        $pendingDues = $this->getPendingDues($branch_id);

        // Recent transactions
        $recentPayments = Payment::with(['customer', 'monthlyCharge'])
            ->when($branch_id, function($q) use ($branch_id) {
                $q->whereHas('customer.bookings.bed.room', function($q2) use ($branch_id) {
                    $q2->where('branch_id', $branch_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $branches = Branch::all();

        return view('admin.reports.index', compact(
            'totalRevenue', 'revenueGrowth', 'monthlyRevenue', 'revenueByBranch',
            'occupancyData', 'customerStats', 'paymentStats', 'pendingDues',
            'recentPayments', 'branches', 'period', 'year', 'month', 'branch_id',
            'startDate', 'endDate'
        ));
    }

    private function getRevenue($startDate, $endDate, $branch_id = null)
    {
        return Payment::whereIn('status', ['completed', 'success', 'paid'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($branch_id, function($q) use ($branch_id) {
                $q->whereHas('customer.bookings.bed.room', function($q2) use ($branch_id) {
                    $q2->where('branch_id', $branch_id);
                });
            })
            ->sum('amount');
    }

    private function getMonthlyRevenue($branch_id = null)
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = Payment::whereIn('status', ['completed', 'success', 'paid'])
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->when($branch_id, function($q) use ($branch_id) {
                    $q->whereHas('customer.bookings.bed.room', function($q2) use ($branch_id) {
                        $q2->where('branch_id', $branch_id);
                    });
                })
                ->sum('amount');
            
            $data[] = [
                'month' => $date->format('M Y'),
                'revenue' => $revenue
            ];
        }
        return $data;
    }

    private function getRevenueByBranch($startDate, $endDate)
    {
        return Branch::withCount(['rooms'])->get()->map(function($branch) use ($startDate, $endDate) {
            $revenue = Payment::whereIn('status', ['completed', 'success', 'paid'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereHas('customer.bookings.bed.room', function($q) use ($branch) {
                    $q->where('branch_id', $branch->id);
                })
                ->sum('amount');
            
            return [
                'name' => $branch->name,
                'revenue' => $revenue,
                'rooms' => $branch->rooms_count
            ];
        });
    }

    private function getOccupancyData($branch_id = null)
    {
        $totalBeds = DB::table('beds')
            ->join('rooms', 'beds.room_id', '=', 'rooms.id')
            ->when($branch_id, function($q) use ($branch_id) {
                $q->where('rooms.branch_id', $branch_id);
            })
            ->count();

        $occupiedBeds = DB::table('beds')
            ->join('rooms', 'beds.room_id', '=', 'rooms.id')
            ->where('beds.status', 'occupied')
            ->when($branch_id, function($q) use ($branch_id) {
                $q->where('rooms.branch_id', $branch_id);
            })
            ->count();

        return [
            'total' => $totalBeds,
            'occupied' => $occupiedBeds,
            'available' => $totalBeds - $occupiedBeds,
            'rate' => $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0
        ];
    }

    private function getCustomerStats($startDate, $endDate, $branch_id = null)
    {
        $query = Customer::query();
        
        if ($branch_id) {
            $query->whereHas('bookings.bed.room', function($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            });
        }

        return [
            'total' => $query->count(),
            'new' => $query->clone()->whereBetween('created_at', [$startDate, $endDate])->count(),
            'active' => $query->clone()->whereHas('bookings', function($q) {
                $q->where('status', 'active');
            })->count(),
        ];
    }

    private function getPaymentStats($startDate, $endDate, $branch_id = null)
    {
        $query = Payment::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($branch_id) {
            $query->whereHas('customer.bookings.bed.room', function($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            });
        }

        return [
            'total_count' => $query->clone()->count(),
            'completed' => $query->clone()->whereIn('status', ['completed', 'success', 'paid'])->count(),
            'pending' => $query->clone()->where('status', 'pending')->count(),
            'failed' => $query->clone()->where('status', 'failed')->count(),
            'total_amount' => $query->clone()->whereIn('status', ['completed', 'success', 'paid'])->sum('amount'),
        ];
    }

    private function getPendingDues($branch_id = null)
    {
        return MonthlyCharge::where('status', 'pending')
            ->when($branch_id, function($q) use ($branch_id) {
                $q->whereHas('customer.bookings.bed.room', function($q2) use ($branch_id) {
                    $q2->where('branch_id', $branch_id);
                });
            })
            ->sum('total_amount');
    }

    public function export(Request $request)
    {
        // CSV export functionality
        $period = $request->get('period', 'month');
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $payments = Payment::with(['customer', 'monthlyCharge'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "revenue_report_{$year}_{$month}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Customer', 'Type', 'Amount', 'Payment ID', 'Status']);
            
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->created_at->format('Y-m-d H:i'),
                    $payment->customer->name ?? 'N/A',
                    $payment->monthlyCharge->charge_type ?? 'Payment',
                    $payment->amount,
                    $payment->razorpay_payment_id ?? 'N/A',
                    $payment->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
