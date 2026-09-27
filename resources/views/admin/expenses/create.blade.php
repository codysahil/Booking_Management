@extends('layouts.admin')

@section('header', 'Record Expense')

@section('content')
    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('admin.expenses.store') }}" enctype="multipart/form-data"
            x-data="{ submitting: false }" @submit="submitting = true">
            <x-form.card title="Expense Details">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-9m-2 9h10a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </x-slot:icon>
                @include('admin.expenses._form')
            </x-form.card>
        </form>
    </div>
@endsection
