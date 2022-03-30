<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-02
 * Time: 23:19
 * Description:
 */

namespace app\admin\service;


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
            Cache::tag('menu_list')->clear();
        } else {
            Cache::delete('menu_list_' . $admin_id);
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

    /**
     * 更新系统设置缓存
     * @return bool
     */
    public static function updateSystemConfig(): bool
    {
        Cache::tag('sysconfig')->clear();
        return true;
    }
}