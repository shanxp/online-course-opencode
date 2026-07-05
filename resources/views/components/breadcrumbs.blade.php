@php
    $segments = request()->segments();
    $breadcrumbs = [];
    $url = '';

    foreach ($segments as $segment) {
        $url .= '/' . $segment;
        $breadcrumbs[] = [
            'label' => ucwords(str_replace(['_', '-'], ' ', $segment)),
            'url' => $url,
        ];
    }
@endphp

<nav class="flex text-sm text-gray-500">
    @foreach($breadcrumbs as $index => $crumb)
        @if($index > 0)
            <span class="mx-2">/</span>
        @endif
        @if($index === count($breadcrumbs) - 1)
            <span class="text-gray-900 font-medium">{{ $crumb['label'] }}</span>
        @else
            <a href="{{ $crumb['url'] }}" class="hover:text-gray-700">{{ $crumb['label'] }}</a>
        @endif
    @endforeach
</nav>
