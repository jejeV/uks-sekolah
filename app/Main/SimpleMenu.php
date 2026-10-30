<?php

namespace App\Main;

class SimpleMenu
{
    /**
     * List of simple menu items.
     *
     * @return array
     */
    public static function menu()
    {
        return SideMenu::menu();
    }
}
