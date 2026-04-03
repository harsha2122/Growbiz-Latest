@php
    $colors = [
        'ACTIVE' => 'success',
        'PAUSED' => 'warning',
        'DELETED' => 'danger',
        'IN_REVIEW' => 'info',
        'REJECTED' => 'danger',
    ];
    $color = $colors[$status] ?? 'secondary';
@endphp
<span class="badge bg-{{ $color }}">{{ $status }}</span>
