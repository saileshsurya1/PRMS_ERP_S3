@php
$user = Auth::user();
@endphp
<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)

    @php
      $menuUrl = is_array($submenu->url ?? null) ? reset($submenu->url) : ($submenu->url ?? null);
      $menuName = $submenu->name ?? '';
      $isAdminRoute = ($menuUrl && str_starts_with((string)$menuUrl, 'admin.')) || in_array($menuName, ['System Configuration', 'Master menus', 'Admin Dashboard', 'User Management', 'Departments'], true);
      $isAuthorized = true;
      if ($isAdminRoute) {
        $isAuthorized = $user && $user->isAdmin();
      } elseif (isset($submenu->roles) && is_array($submenu->roles)) {
        $isAuthorized = $user && (in_array($user->role, $submenu->roles, true) || $user->isAdmin());
      } elseif (isset($submenu->adminOnly) && $submenu->adminOnly) {
        $isAuthorized = $user && $user->isAdmin();
      }
    @endphp

    @if ($isAuthorized)
      {{-- active menu method --}}
      @php
        $activeClass = null;
        $active = $configData["layout"] === 'vertical' ? 'active open':'active';
        $currentRouteName = (string) Route::currentRouteName();
        $menuSlugs = is_array($submenu->slug ?? null) ? $submenu->slug : [$submenu->slug ?? ''];

        if (in_array($currentRouteName, $menuSlugs, true)) {
            $activeClass = 'active';
        }
        elseif (isset($submenu->submenu)) {
          foreach ($menuSlugs as $slug) {
            if ($slug !== '' && str_starts_with($currentRouteName, (string) $slug)) {
              $activeClass = $active;
              break;
            }
          }
        }
      @endphp

        <li class="menu-item {{$activeClass}}">
          <a href="{{ $menuUrl ? (Route::has($menuUrl) ? route($menuUrl) : url($menuUrl)) : 'javascript:void(0)' }}" class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
            @if (isset($submenu->icon))
            <i class="{{ $submenu->icon }}"></i>
            @endif
            <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
            @isset($submenu->badge)
              <div class="badge bg-{{ $submenu->badge[0] }} rounded-pill ms-auto">{{ $submenu->badge[1] }}</div>
            @endisset
          </a>

          {{-- submenu --}}
          @if (isset($submenu->submenu))
            @include('layouts.sections.menu.submenu',['menu' => $submenu->submenu])
          @endif
        </li>
      @endif
    @endforeach
  @endif
</ul>
