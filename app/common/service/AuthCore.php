<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-30
 * Time: 14:10
 * Description:
 */

namespace app\common\service;


use app\admin\model\Manage;
use app\admin\model\Menu;
use app\common\constants\AdminConstant;
use think\facade\Cache;

/**
 * Class AuthGroup
 * @package app\common\service
 */
class AuthCore
{
    private static $instance = null;
    /**
     * 管理员id
     * @var mixed
     */
    private $admin_id;

    /**
     * 权限模块
     * @var mixed
     */
    private $authModel;

    /**
     * 实例化入口
     * @return AuthCore|null
     */
    public static function getInstance(): AuthCore
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $model = app('http')->getName();
        $this->authModel = $model;
        $this->admin_id = $model == 'backend' ? request()->manage_id : session('admin.manage_id');
    }

    private function __clone()
    {
    }

    /**
     * 获取个人菜单
     * @return array|mixed|object|\think\App
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getMenu()
    {
        $data = cache('menu_list_' . $this->admin_id);

        if (!$data) {
            $rules = self::ownAuth();

            $condition = [
                ['status', '=', 1],
                ['deep', 'in', '1,2,3']
            ];
            if ($rules != -1) {
                $condition[] = ['id', 'in', $rules];
            }

            $menu = Menu
                ::where($condition)
                ->field('id,pid,title,icon,href,target')
                ->order('sort', 'desc')
                ->select();

            $top = Menu::where('pid', AdminConstant::MENU_HOME_ID)->field('id,pid,title,href')->find();

            $data = [
                'homeInfo' => [
                    'title' => $top['title'],
                    "href"  => $top['href']
                ],
                'logoInfo' => [
                    "title" => sysconfig('site', 'site_title'),
                    "image" => filePathJoin(sysconfig('site', 'site_logo')) ?: request()->domain() . "/static/admin/images/logo.png",
                ],
                'menuInfo' => buildMenuChild($menu, 0)
            ];

            Cache::tag('menu_list')->set('menu_list_' . $this->admin_id, $data);
        }

        return $data;
    }

    /**
     * 获取后台菜单名
     * @param $manage_id
     * @return mixed|object|\think\App
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getBackendMenu($manage_id)
    {
        $data = cache('backend_menu_list_' . $manage_id);

        if (!$data) {
            $rules = self::ownAuth($manage_id);

            $condition = [
                ['status', '=', 1],
                ['deep', 'in', '2,3']
            ];
            if ($rules != -1) {
                $condition[] = ['id', 'in', $rules];
            }

            $menu = Menu::where($condition)->column('title');

            $data = [
                'logo' => [
                    'title' => sysconfig('site', 'site_title'),
                    'image' => filePathJoin(sysconfig('site', 'site_logo')) ?: request()->domain() . "/static/admin/images/logo.png"
                ],
                'menu' => $menu
            ];

            Cache::tag('backend_menu_list')->set('backend_menu_list_' . $manage_id, $data);
        }

        return $data;
    }

    /**
     * 验证权限
     * @param $url
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function verifyAuth($url): bool
    {
        if ($this->admin_id != AdminConstant::SUPER_ADMIN_ID) {
            $userAuthArr = self::generateAuthData();
            if ($userAuthArr) {
                // 提取链接
                $userAuthArr = array_filter(array_column($userAuthArr, 'href'));
            }
            return in_array($url, $userAuthArr);
        }
        return true;
    }

    /**
     * 生成权限数据
     * @return array|mixed|object|\think\App
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function generateAuthData()
    {
        $prefix = $this->authModel == 'backend' ? 'backend_user_auth_' : 'admin_user_auth_';
        $tag = $this->authModel == 'backend' ? 'backend_user_auth' : 'admin_user_auth';
        $userAuth = cache($prefix . $this->admin_id);

        if (!$userAuth) {
            // 查询用户权组所含权限
            $userIdStr = self::ownAuth();
            // 所有权限
            $allAuth = self::allAuthData();
            // 转化完的用户权限数组
            $userAuth = [];
            if ($allAuth) {
                if ($userIdStr == -1) {
                    $userAuth = $allAuth;
                } else {
                    $userIdStr = array_flip(explode(',', $userIdStr));
                    $userAuth = array_filter($allAuth, function ($value) use ($userIdStr) {
                        return array_key_exists($value['id'], $userIdStr);
                    });
                }
            }
            Cache::tag($tag)->set($prefix . $this->admin_id, $userAuth);
        }

        return $userAuth;
    }

    /**
     * 全部权限
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function allAuthData(): array
    {
        return Menu
            ::where('status', 1)
            ->field('id,title,href')
            ->select()
            ->toArray();
    }

    /**
     * 自己拥有的所有权限
     * @param $manage_id
     * @return mixed
     */
    private function ownAuth($manage_id = null)
    {
        return Manage
            ::withJoin(['manage_role' => ['rules']])
            ->where([
                ['status', '=', 1],
                ['manage_id', '=', $manage_id ?: $this->admin_id]
            ])
            ->value('rules', '');
    }
}