<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Main\TopMenu;
use App\Main\SideMenu;
use App\Main\SimpleMenu;
use Illuminate\Support\Str;

class MenuComposer
{
    protected static $cachedData = [];

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if (!is_null(request()->route())) {
            $pageName = request()->route()->getName();
            $layout = $this->layout($view);
            $cacheKey = $pageName . '|' . $layout;

            if (!isset(self::$cachedData[$cacheKey])) {
                $activeMenu = $this->activeMenu($pageName, $layout);
                self::$cachedData[$cacheKey] = [
                    'top_menu' => TopMenu::menu(),
                    'side_menu' => SideMenu::menu(),
                    'simple_menu' => SimpleMenu::menu(),
                    'first_level_active_index' => $activeMenu['first_level_active_index'],
                    'second_level_active_index' => $activeMenu['second_level_active_index'],
                    'third_level_active_index' => $activeMenu['third_level_active_index'],
                ];
            }

            $data = self::$cachedData[$cacheKey];
            $view->with('top_menu', $data['top_menu']);
            $view->with('side_menu', $data['side_menu']);
            $view->with('simple_menu', $data['simple_menu']);
            $view->with('first_level_active_index', $data['first_level_active_index']);
            $view->with('second_level_active_index', $data['second_level_active_index']);
            $view->with('third_level_active_index', $data['third_level_active_index']);
            $view->with('page_name', $pageName);
            $view->with('layout', $layout);
        }
    }

    /**
     * Specify used layout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function layout($view)
    {
        if (isset($view->layout)) {
            return $view->layout;
        } else if (request()->has('layout')) {
            return request()->query('layout');
        }

        return 'side-menu';
    }

    /**
     * Determine active menu & submenu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function activeMenu($pageName, $layout)
    {
        $menus = [
            'top-menu' => fn () => TopMenu::menu(),
            'simple-menu' => fn () => SimpleMenu::menu(),
        ];
        $active = ['', '', ''];

        foreach (($menus[$layout] ?? fn () => SideMenu::menu())() as $menuKey => $menu) {
            if (!is_array($menu)) {
                continue;
            }

            if ($this->matchesRoute($menu, $pageName)) {
                $active = [$menuKey, '', ''];
            }

            foreach ($menu['sub_menu'] ?? [] as $subMenuKey => $subMenu) {
                if ($this->matchesRoute($subMenu, $pageName)) {
                    $active = [$menuKey, $subMenuKey, ''];
                }

                foreach ($subMenu['sub_menu'] ?? [] as $lastSubMenuKey => $lastSubMenu) {
                    if ($this->matchesRoute($lastSubMenu, $pageName)) {
                        $active = [$menuKey, $subMenuKey, $lastSubMenuKey];
                    }
                }
            }
        }

        return [
            'first_level_active_index' => $active[0],
            'second_level_active_index' => $active[1],
            'third_level_active_index' => $active[2],
        ];
    }

    /**
     * Menu tetap aktif di semua halaman dalam modul yang sama (index, show, edit, create).
     */
    private function matchesRoute(array $menu, ?string $pageName): bool
    {
        if (!isset($menu['route_name']) || !$pageName) {
            return false;
        }

        return $menu['route_name'] === $pageName
            || Str::before($menu['route_name'], '.') === Str::before($pageName, '.');
    }
}
