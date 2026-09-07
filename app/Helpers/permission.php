<?php

use App\Models\Menu;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Auth;

function can($menuName, $action)
{
    $user = Auth::user();
    if (! $user) {
        return false;
    }

    $menu = Menu::where('name', $menuName)->first();
    if (! $menu) {
        return false;
    }

    $perm = RolePermission::where('role_id', $user->role_id)
        ->where('menu_id', $menu->id)
        ->first();

    if (! $perm) {
        return false;
    }

    return $perm->{'can_'.$action} ?? false;
}
