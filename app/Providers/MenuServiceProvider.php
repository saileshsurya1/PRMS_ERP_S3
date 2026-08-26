<?php

namespace App\Providers;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (app()->runningInConsole()) {
            view()->share('menuData', $this->staticMenu());
            return;
        }

        view()->composer('*', function ($view): void {
            if (!Schema::hasTable('menu_items')) {
                $view->with('menuData', $this->staticMenu());
                return;
            }

            $menus = MenuItem::with('children')->whereNull('parent_id')->where('is_active', true)
                ->visibleTo(Auth::user())->orderBy('sort_order')->get();
            $items = $menus->map(fn (MenuItem $menu) => $this->toThemeItem($menu));
            $view->with('menuData', [(object) ['menu' => $items], (object) ['menu' => []]]);
        });
    }

    private function staticMenu(): array
    {
        return [
            json_decode(file_get_contents(base_path('resources/menu/verticalMenu.json'))),
            json_decode(file_get_contents(base_path('resources/menu/horizontalMenu.json'))),
        ];
    }

    private function toThemeItem(MenuItem $menu): object
    {
        $item = (object) [
            'name' => $menu->label,
            'slug' => $menu->route,
            'url' => $menu->route,
            'icon' => $menu->icon,
        ];

        if ($menu->relationLoaded('children') && $menu->children->isNotEmpty()) {
            $item->submenu = $menu->children->where('is_active', true)->map(fn (MenuItem $child) => $this->toThemeItem($child))->all();
        }

        return $item;
    }
}