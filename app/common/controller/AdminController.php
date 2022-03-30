<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-06-19
 * Time: 11:00
 * Description:
 */

namespace app\common\controller;


use app\BaseController;
use app\common\traits\JumpTrait;

class AdminController extends BaseController
{
    use JumpTrait;

    /**
     * 模板布局, false取消
     * @var string|bool
     */
    protected $layout = 'layout/layout';

    /**
     * @var mixed 管理员id
     */
    protected $admin_id = null;

    /**
     * 下拉选择条件
     * @var array
     */
    protected $selectWhere = [];

    /**
     * 是否关联查询
     * @var bool
     */
    protected $relationSearch = false;


    public function initialize()
    {
        parent::initialize();
        $this->layout && $this->app->view->engine()->layout($this->layout);
        $this->admin_id = session('admin.manage_id');
    }

    /**
     * 模板变量赋值
     * @param string|array $name 模板变量
     * @param mixed $value 变量值
     * @return mixed
     */
    public function assign($name, $value = null)
    {
        return $this->app->view->assign($name, $value);
    }

    /**
     * 解析和获取模板内容 用于输出
     * @param string $template
     * @param array $vars
     * @return mixed
     */
    public function fetch(string $template = '', array $vars = [])
    {
        return $this->app->view->fetch($template, $vars);
    }

    /**
     * 构建请求参数
     * @param null $model 要查询的模型
     * @param array $excludeFields 忽略构建搜索的字段
     * @return array
     */
    protected function buildTableParam($model = null, array $excludeFields = []): array
    {
        $get = $this->request->get('', null, null);
        $page = isset($get['page']) && !empty($get['page']) ? $get['page'] : 1;
        $limit = isset($get['limit']) && !empty($get['limit']) ? $get['limit'] : 15;
        $filters = isset($get['filter']) && !empty($get['filter']) ? $get['filter'] : '{}';
        $ops = isset($get['op']) && !empty($get['op']) ? $get['op'] : '{}';
        // json转数组
        $filters = json_decode($filters, true);
        $ops = json_decode($ops, true);
        $where = [];
        $excludes = [];

        $model = new $model();
        // 判断是否关联查询
        $tableName = humpToLine(lcfirst($model->getName()));

        foreach ($filters as $key => $val) {
            if (in_array($key, $excludeFields)) {
                $excludes[$key] = $val;
                continue;
            }
            $op = isset($ops[$key]) && !empty($ops[$key]) ? $ops[$key] : '%*%';
            if ($this->relationSearch && count(explode('.', $key)) == 1) {
                $key = "{$tableName}.{$key}";
            }
            switch (strtolower($op)) {
                case '=':
                    $where[] = [$key, '=', $val];
                    break;
                case '%*%':
                    $where[] = [$key, 'LIKE', "%{$val}%"];
                    break;
                case '*%':
                    $where[] = [$key, 'LIKE', "{$val}%"];
                    break;
                case '%*':
                    $where[] = [$key, 'LIKE', "%{$val}"];
                    break;
                case 'range':
                    [$beginTime, $endTime] = explode(' - ', $val);
                    $where[] = [$key, '>=', strtotime($beginTime)];
                    $where[] = [$key, '<=', strtotime($endTime)];
                    break;
                default:
                    $where[] = [$key, $op, "%{$val}"];
            }
        }
        return [$limit, $where, $excludes, $page];
    }


    /**
     * 下拉选择列表
     * @param $model
     * @return array|false|mixed|\think\response\Json|\think\response\View
     */
    public function selectList($model)
    {
        $fields = input('selectFields');
        $data = (new $model())
            ->where($this->selectWhere)
            ->field($fields)
            ->select();
        return $this->success($data);
    }
}