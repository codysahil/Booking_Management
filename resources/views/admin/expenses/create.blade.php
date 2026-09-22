@extends('layouts.admin')

@section('header', 'Record Expense')

@section('content')
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
        <form method="POST" action="{{ route('admin.expenses.store') }}" enctype="multipart/form-data">
            @include('admin.expenses._form')
        </form>
    </div>
@endsection
