@csrf

<div class="space-y-5">
    <x-form.input name="title" label="Title" required :value="$announcement->title ?? ''" />
    <x-form.textarea name="body" label="Message" :rows="5" required :value="$announcement->body ?? ''" />

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <x-form.select name="branch_id" label="Branch">
            <option value="">All branches</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" {{ old('branch_id', $announcement->branch_id ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.input name="expires_on" label="Expires On" type="date"
            :value="old('expires_on', isset($announcement) && $announcement->expires_on ? $announcement->expires_on->format('Y-m-d') : '')" />
    </div>

    <x-form.toggle name="is_pinned" label="Pin to top" hint="Shown above all other announcements on residents' dashboards"
        :checked="$announcement->is_pinned ?? false" />
</div>

<div class="mt-8 flex justify-end gap-3">
    <x-form.link-button :href="route('admin.announcements.index')">Cancel</x-form.link-button>
    <x-form.button :label="isset($announcement) ? 'Update Announcement' : 'Publish Announcement'" loading-label="Publishing…" />
</div>
