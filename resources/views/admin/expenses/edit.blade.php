@extends('layouts.admin')

@section('header', 'Edit Expense')

@section('content')
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
        <form method="POST" action="{{ route('admin.expenses.update', $expense) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.expenses._form')
        </form>
    </div>
@endsection
