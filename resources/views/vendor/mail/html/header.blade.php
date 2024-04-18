@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'UISI Digiclass')
                <img src="{{asset('img/logo.png')}}" class="logo" alt="UISI DIGICLASS Logo">
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
