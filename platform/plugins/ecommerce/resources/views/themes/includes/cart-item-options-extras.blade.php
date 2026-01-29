@if (($extras = Arr::get($options, 'extras', [])) && is_array($extras))
    @if (!empty($extras['custom_variation']['name']))
        <p class="mb-0">
            <small>{{ __('Variation') }}: <strong>{{ $extras['custom_variation']['name'] }}</strong></small>
        </p>
    @endif
    @foreach ($extras as $key => $extra)
        @if ($key !== 'custom_variation' && !empty($extra['key']) && !empty($extra['value']))
            <p class="mb-0">
                <small>{{ $extra['key'] }}: <strong> {{ $extra['value'] }}</strong></small>
            </p>
        @endif
    @endforeach
@endif
