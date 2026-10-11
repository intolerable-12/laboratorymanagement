@php
    $status = $forumPost->is_hidden
        ? ['label' => 'Hidden', 'tone' => 'danger', 'icon' => 'fa-eye-slash']
        : ($forumPost->is_locked
            ? ['label' => 'Locked', 'tone' => 'secondary', 'icon' => 'fa-lock']
            : ($forumPost->is_pinned
                ? ['label' => 'Pinned', 'tone' => 'primary', 'icon' => 'fa-thumbtack']
                : ['label' => 'Open', 'tone' => 'success', 'icon' => 'fa-door-open']));
@endphp

<span class="category-chip">
    <i class="fa-solid fa-tag me-1" aria-hidden="true"></i>{{ $forumPost->category ?? 'General' }}
</span>
<span class="badge rounded-pill forum-status-badge text-bg-{{ $status['tone'] }}">
    <i class="fa-solid {{ $status['icon'] }}" aria-hidden="true"></i>{{ $status['label'] }}
</span>
