<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022/4/14
 * Time: 9:49
 * Description:
 */

namespace app\mobile\controller;

use app\common\controller\MobileController;

class Index extends MobileController
{
    /**
     * 首页(选择平台)
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 首页
     * @return mixed
     */
    public function home()
    {
        return $this->fetch('home/index');
    }
}