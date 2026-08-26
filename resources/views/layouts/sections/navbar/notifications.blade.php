@php
  $notifications = auth()->user()->notifications()->latest()->limit(8)->get();
  $unreadNotifications = auth()->user()->unreadNotifications()->count();
@endphp
<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1">
  <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
    <i class="mdi mdi-bell-outline mdi-24px"></i>
    @if($unreadNotifications > 0)<span class="position-absolute top-0 start-50 translate-middle-y badge badge-dot bg-danger mt-2 border"></span>@endif
  </a>
  <ul class="dropdown-menu dropdown-menu-end py-0">
    <li class="dropdown-menu-header border-bottom"><div class="dropdown-header d-flex align-items-center py-3"><h6 class="mb-0 me-auto">Notifications</h6><span class="badge rounded-pill bg-label-primary">{{ $unreadNotifications }} New</span></div></li>
    <li class="dropdown-notifications-list scrollable-container"><ul class="list-group list-group-flush">
      @forelse($notifications as $notification)
        <li class="list-group-item list-group-item-action dropdown-notifications-item {{ $notification->read_at ? 'marked-as-read' : '' }}"><a class="d-flex gap-2 text-reset text-decoration-none" href="{{ route('notifications.read', $notification) }}"><div class="flex-shrink-0"><div class="avatar me-1"><span class="avatar-initial rounded-circle bg-label-{{ $notification->data['color'] ?? 'primary' }}"><i class="{{ $notification->data['icon'] ?? 'mdi mdi-bell-outline' }}"></i></span></div></div><div class="d-flex flex-column flex-grow-1 overflow-hidden"><h6 class="mb-1 text-truncate">{{ $notification->data['title'] ?? 'Notification' }}</h6><small class="text-truncate text-body">{{ $notification->data['message'] ?? '' }}</small><small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small></div></a></li>
      @empty
        <li class="list-group-item text-muted">No notifications</li>
      @endforelse
    </ul></li>
    @if($notifications->isNotEmpty())<li class="dropdown-menu-footer border-top p-2"><form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-primary d-flex justify-content-center w-100">Mark all as read</button></form></li>@endif
  </ul>
</li>