{{--
    Reusable detail row partial for show pages.
    Usage: @include('employees._detail_row', ['label' => 'Label', 'value' => 'Value'])
    Optional: 'mono' => true  (monospace font)
              'bold' => true  (bold value)
--}}
<div style="display:flex;justify-content:space-between;align-items:baseline;
            padding:7px 0;border-bottom:1px solid var(--border);
            font-size:0.875rem;">
    <span style="color:var(--text-light);font-weight:600;
                 letter-spacing:0.02em;min-width:160px;">
        {{ $label }}
    </span>
    <span style="text-align:right;
                 {{ isset($mono) && $mono ? 'font-family:monospace;' : '' }}
                 {{ isset($bold) && $bold ? 'font-weight:700;color:var(--navy);' : 'color:var(--text);' }}">
        {{ $value }}
    </span>
</div>