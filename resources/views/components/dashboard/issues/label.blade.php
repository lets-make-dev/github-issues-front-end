@props(['label', 'color'])

{{--<span class="text-xs font-semibold px-2 py-1 rounded border--}}
{{--    @if($label === 'Feature') bg-blue-100 text-blue-800 border-blue-800--}}
{{--    @elseif($label === 'Bug') bg-red-100 text-red-800 border-red-800--}}
{{--    @elseif($label === 'UI') bg-purple-100 text-purple-800 border-purple-800--}}
{{--    @elseif($label === 'Security') bg-yellow-100 text-yellow-800 border-yellow-800--}}
{{--    @elseif($label === 'Performance') bg-indigo-100 text-indigo-800 border-indigo-800--}}
{{--    @elseif($label === 'Backend') bg-pink-100 text-pink-800 border-pink-800--}}
{{--    @else bg-gray-100 text-gray-800 border-gray-800--}}
{{--    @endif">--}}
{{--    {{ $label }}--}}
{{--</span>--}}
<span class="text-xs font-bold px-2 py-1 rounded border" style="background-color: {{ "#$color" }}; border-color: {{ "#$color" }}">
    {{ $label }}
</span>