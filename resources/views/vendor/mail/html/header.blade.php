@props(['url'])
<style>
    @import url('https://fonts.googleapis.com/css?family=Shadows Into Light');
</style>
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            <img src="{{ asset('/icon.png') }}" class="logo" alt="Laravel Logo">
        </a>
    </td>
</tr>
