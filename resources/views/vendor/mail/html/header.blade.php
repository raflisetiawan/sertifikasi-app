@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'UISI Digiclass')
                <img src="https://qcbkp.tech/assets/logo_bkp.6982b6d9.png" class="logo" alt="UISI DIGICLASS Logo">
            @else
                {{ $slot }}
            @endif
        </a>
    </td>
</tr>
