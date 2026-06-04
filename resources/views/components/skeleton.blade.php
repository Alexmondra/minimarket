@props(['class' => '', 'count' => 1])

@for ($i = 0; $i < $count; $i++)
    <div class="skeleton {{ $class }}" {{ $attributes->except('class') }}></div>
@endfor
