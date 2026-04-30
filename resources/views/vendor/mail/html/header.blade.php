@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
<img src="{{ asset('images/logo.png') }}" class="logo" alt="{{ config('app.name') }}">
<div style="margin-top: 12px; color: #1e3a8a; font-size: 18px; font-weight: 700; letter-spacing: -0.01em;">
{{ config('app.name') }}
</div>
</a>
</td>
</tr>
