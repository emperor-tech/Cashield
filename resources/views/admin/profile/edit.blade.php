@extends('layouts.admin')

@section('page_title', 'Admin Profile')

@section('content')
<div class="max-w-2xl mx-auto py-10">
    <h2 class="text-2xl font-bold mb-6">Your Admin Profile</h2>
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-6">
        @csrf
        @method('PATCH')
        <div>
            <label class="form-label">Name</label>
            <input type="text" name="name" class="admin-input w-full" value="{{ old('name', $admin->name) }}" required>
        </div>
        <div>
            <label class="form-label">Email</label>
            <input type="email" name="email" class="admin-input w-full" value="{{ old('email', $admin->email) }}" required>
        </div>
        <div>
            <label class="form-label">New Password <span class="text-xs text-gray-400">(leave blank to keep current)</span></label>
            <input type="password" name="password" class="admin-input w-full">
        </div>
        <div>
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="admin-input w-full">
        </div>
        <div class="flex justify-end">
            <button type="submit" class="admin-btn-primary">Update Profile</button>
        </div>
    </form>
</div>
@endsection 