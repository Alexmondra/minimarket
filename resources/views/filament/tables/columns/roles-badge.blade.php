<div class="flex flex-wrap gap-1">
    @foreach($getRecord()->roles as $role)
        <x-filament::badge color="success">
            {{ $role->name }}
        </x-filament::badge>
    @endforeach
</div>
