@extends('layouts.admin')

@section('header', 'Edit Announcement')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg border-2 border-gray-100 p-8">
        <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}">
            @method('PUT')
            @include('admin.announcements._form')
        </form>
    </div>
@endsection
