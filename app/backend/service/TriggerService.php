<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-09-14
 * Time: 15:43
 * Description:
 */

namespace app\backend\service;

use think\facade\Cache;

class TriggerService
{
    /**
     * 更新菜单缓存
     * @param null $admin_id
     * @return bool
     */
    public static function updateMenu($admin_id = null): bool
    {
        if (!$admin_id) {
            Cache::tag('backend_menu_list')->clear();
        } else {
            Cache::delete('backend_menu_list_' . $admin_id);
        }
        return true;
    }

    /**
     * 更新权限缓存
     * @param null $admin_id
     * @return bool
     */
    public static function updateAuth($admin_id = null): bool
    {
        if (!$admin_id) {
            Cache::tag('admin_user_auth')->clear();
        } else {
            Cache::delete('admin_user_auth_' . $admin_id);
        }
        return true;
    }
}