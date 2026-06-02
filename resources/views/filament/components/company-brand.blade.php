<div class="company-brand">
    <div class="company-brand__mark">
        @if ($companyLogoUrl)
            <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}" class="company-brand__logo">
        @else
            <span class="company-brand__initials">{{ $companyInitials }}</span>
        @endif
    </div>

    <div class="company-brand__copy">
        <span class="company-brand__name">{{ $companyShortName }}</span>
        <span class="company-brand__meta">
            {{ $sucursalName ?: ($isGlobalView ? 'Vista global de la empresa' : 'Panel administrativo') }}
        </span>
    </div>
</div>
