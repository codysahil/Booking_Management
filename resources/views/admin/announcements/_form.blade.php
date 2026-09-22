@csrf
<div class="space-y-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Title *</label>
        <input type="text" name="title" required value="{{ old('title', $announcement->title ?? '') }}"
            class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
        @error('title') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Message *</label>
        <textarea name="body" rows="5" required class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">{{ old('body', $announcement->body ?? '') }}</textarea>
        @error('body') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Branch</label>
            <select name="branch_id" class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
                <option value="">All branches</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $announcement->branch_id ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Expires On</label>
            <input type="date" name="expires_on" value="{{ old('expires_on', isset($announcement) && $announcement->expires_on ? $announcement->expires_on->format('Y-m-d') : '') }}"
                class="w-full border-gray-300 rounded-xl focus:ring-primary-500 focus:border-primary-500">
        </div>
    </div>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', $announcement->is_pinned ?? false) ? 'checked' : '' }}
            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
        <span class="text-sm text-gray-700">Pin to the top of residents' dashboards</span>
    </label>
</div>

<div class="mt-8 flex gap-3">
    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-teal-500 to-cyan-500 text-white font-bold rounded-xl hover:from-teal-600 hover:to-cyan-600 transition shadow-lg">
        {{ isset($announcement) ? 'Update Announcement' : 'Publish Announcement' }}
    </button>
    <a href="{{ route('admin.announcements.index') }}" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 transition">Cancel</a>
</div>
