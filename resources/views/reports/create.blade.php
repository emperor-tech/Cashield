@extends('layouts.app')

@section('content')
<h1 class="text-2xl mb-4">Report a Crime</h1>
<form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-6 shadow rounded" aria-label="Report a Crime Form">
  @csrf
  @guest
  <div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-200" for="guest_name">Your Name (optional)</label>
    <input type="text" name="guest_name" id="guest_name" class="w-full border p-2 dark:bg-gray-900 dark:text-gray-100" autocomplete="name">
  </div>
  <div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-200" for="guest_email">Your Email (optional)</label>
    <input type="email" name="guest_email" id="guest_email" class="w-full border p-2 dark:bg-gray-900 dark:text-gray-100" autocomplete="email">
  </div>
  @endguest
  <div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-200" for="campus">Campus</label>
    <input type="text" name="campus" id="campus" class="w-full border p-2 dark:bg-gray-900 dark:text-gray-100" required>
  </div>
  <div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-200" for="location">Location</label>
    <input type="text" name="location" id="location" class="w-full border p-2 dark:bg-gray-900 dark:text-gray-100" required>
  </div>
  <div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-200" for="description">Description</label>
    <textarea name="description" id="description" class="w-full border p-2 dark:bg-gray-900 dark:text-gray-100" rows="4" required></textarea>
  </div>
  <div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-200" for="severity">Severity</label>
    <select name="severity" id="severity" class="w-full border p-2 dark:bg-gray-900 dark:text-gray-100">
      <option value="low">Low</option>
      <option value="medium">Medium</option>
      <option value="high">High</option>
    </select>
  </div>
  <div class="mb-4">
    <label class="inline-flex items-center text-gray-700 dark:text-gray-200"><input type="checkbox" name="anonymous" class="mr-2"> Report anonymously</label>
  </div>
  <div class="mb-4">
    <label class="block text-gray-700 dark:text-gray-200" for="media">Attach Media (image/video, optional)</label>
    <input type="file" name="media" id="media" accept="image/*,video/*" class="w-full border p-2 dark:bg-gray-900 dark:text-gray-100">
  </div>
  <button type="submit" class="bg-blue-800 dark:bg-blue-600 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">Submit</button>
</form>
@endsection
