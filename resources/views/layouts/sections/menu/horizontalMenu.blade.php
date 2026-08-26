@php
$configData = Helper::appClasses();
$user = Auth::user();
@endphp
<!-- Horizontal Menu -->
<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal  menu bg-menu-theme flex-grow-0">
  <div class="{{$containerNav}} d-flex h-100">
    <ul class="menu-inner">
      @foreach ($menuData[1]->menu as $menu)

      @php
        $menuUrl = is_array($menu->url ?? null) ? reset($menu->url) : ($menu->url ?? null);
        $menuName = $menu->name ?? '';
        $isAdminRoute = ($menuUrl && str_starts_with((string)$menuUrl, 'admin.')) || in_array($menuName, ['System Configuration', 'Master menus', 'Admin Dashboard', 'User Management', 'Departments'], true);
        $isAuthorized = true;
        if ($isAdminRoute) {
          $isAuthorized = $user && $user->isAdmin();
        } elseif (isset($menu->roles) && is_array($menu->roles)) {
          $isAuthorized = $user && (in_array($user->role, $menu->roles, true) || $user->isAdmin());
        } elseif (isset($menu->adminOnly) && $menu->adminOnly) {
          $isAuthorized = $user && $user->isAdmin();
        }
      @endphp

      @if ($isAuthorized)
        {{-- active menu method --}}
        @php
          $activeClass = null;
          $currentRouteName = (string) Route::currentRouteName();
          $menuSlugs = is_array($menu->slug ?? null) ? $menu->slug : [$menu->slug ?? ''];

          if (in_array($currentRouteName, $menuSlugs, true)) {
              $activeClass = 'active';
          }
          elseif (isset($menu->submenu)) {
            foreach ($menuSlugs as $slug) {
              if ($slug !== '' && str_starts_with($currentRouteName, (string) $slug)) {
                $activeClass = 'active';
                break;
              }
            }
          }
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{$activeClass}}">
          <a href="{{ $menuUrl ? (Route::has($menuUrl) ? route($menuUrl) : url($menuUrl)) : 'javascript:void(0);' }}" class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
            @isset($menu->icon)
            <i class="{{ $menu->icon }}"></i>
            @endisset
            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
          </a>

          {{-- submenu --}}
          @isset($menu->submenu)
            @include('layouts.sections.menu.submenu',['menu' => $menu->submenu])
          @endisset
        </li>
      @endif
      @endforeach
    </ul>
  </div>
</aside>
<!--/ Horizontal Menu -->
