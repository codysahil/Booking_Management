<?php

namespace App\Notifications;

use App\Models\Booking;

class NewBookingReceived extends StaffAlert
{
    public function __construct(public Booking $booking, public int $bedCount = 1)
    {
    }

    protected function kind(): string
    {
        return 'booking';
    }

    protected function title(): string
    {
        return 'New online booking ' . $this->booking->booking_reference;
    }

    protected function message(): string
    {
        $customer = $this->booking->customer;

        return sprintf('%s (%s) booked %d bed%s, check-in %s.',
            $customer?->name ?? 'A guest',
            $customer?->phone ?? '—',
            $this->bedCount,
            $this->bedCount === 1 ? '' : 's',
            $this->booking->check_in_date?->format('d M Y') ?? '—');
    }

    protected function url(): string
    {
        return route('admin.bookings.index', ['search' => $this->booking->booking_reference]);
    }
}
