{{-- resources/views/livewire/admin/roles/permissions-matrix.blade.php --}}

<div class="overflow-x-auto">
    <table class="min-w-full">
        <thead>
            <tr>
                <th class="sticky left-0 bg-white">Permission</th>
                @foreach($roles as $role)
                    <th class="px-4 py-2">{{ $role->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($permissions as $permission)
                                <tr>
                                    <td class="sticky left-0 bg-white font-medium">
                                        {{ $permission->name }}
                                    </td>
                                    @foreach($roles as $role)
                                                                <td class="text-center">
                                                                    @if($role->hasPermissionTo($permission))
                                                                        <flux:icon.check-circle class="w-5 h-5 text-green-500 mx-auto" />
                                                                    @else
                                                                        <flux:icon.x-circle class="w-5 h-5 text-gray-300 mx-auto" />
                                                                    @endif
                                        </td>
                                    @endforeach
                </tr>
            @endforeach
</tbody>
</table>
</div>
```