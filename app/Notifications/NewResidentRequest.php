<?php

namespace App\Notifications;

use App\Models\Request as ResidentRequest;

class NewResidentRequest extends StaffAlert
{
    public function __construct(public ResidentRequest $request)
    {
    }

    protected function kind(): string
    {
        return 'request';
    }

    protected function title(): string
    {
        return 'New ' . strtolower($this->request->type_label) . ' request';
    }

    protected function message(): string
    {
        return sprintf('%s (%s): %s',
            $this->request->customer?->name ?? 'A resident',
            $this->request->customer?->customer_code ?? '—',
            $this->request->subject);
    }

    protected function url(): string
    {
        return route('admin.requests.show', $this->request);
    }
}
