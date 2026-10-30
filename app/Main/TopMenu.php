<?php

namespace App\Main;

class TopMenu
{
    /**
     * List of top menu items.
     *
     * @return array
     */
    public static function menu()
    {
        return SideMenu::menu();
    }
}
