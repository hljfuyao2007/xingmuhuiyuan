<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-15
 * Time: 22:01
 * Description:
 */

namespace app\common\service\builder;


class FastBuild
{
    /**
     * @var string 模块
     */
    private $module = 'admin';
    /**
     * @var string 控制器路径名
     */
    private $controllerName;
    /**
     * @var string js路径名
     */
    private $jsName;
    /**
     * @var string 视图路径名
     */
    private $viewName;
    /**
     * @var string 根目录
     */
    private $rootDir;
    /**
     * @var string 分隔符
     */
    private $DS = DIRECTORY_SEPARATOR;
    /**
     * @var string 当前日期
     */
    private $date;
    /**
     * @var string 当前时间
     */
    private $time;
    /**
     * @var string 命名空间
     */
    private $namespace;
    /**
     * @var string 类名
     */
    private $classname;
    /**
     * @var string 控制器文件夹
     */
    private $controllerDir;
    /**
     * @var string 视图文件夹
     */
    private $viewDir;
    /**
     * @var string js文件夹
     */
    private $jsDir;


    public function __construct(string $controllerName)
    {
        $this->rootDir = root_path();
        // 名字斜杠转换
        $controllerName = PHP_OS == 'WINNT' ? str_replace('/', '\\', $controllerName) : str_replace('\\', '/', $controllerName);
        $controllerName = strpos($controllerName, $this->DS) !== 0 ? $this->DS . $controllerName : $controllerName;
        // 处理过的控制器名字
        list($handleControllerName, $lastNameSpace, $this->classname, $dir, $transition) = $this->handleName($controllerName);
        // 拼接控制器名字
        $this->controllerName = "{$this->rootDir}app{$this->DS}{$this->module}{$this->DS}controller{$controllerName}.php";
        // 拼接视图名字
        $this->viewName = "{$this->rootDir}app{$this->DS}{$this->module}{$this->DS}view{$handleControllerName}{$this->DS}index.html";
        // 拼接JS名字
        $this->jsName = "{$this->rootDir}public{$this->DS}static{$this->DS}admin{$this->DS}js{$handleControllerName}.js";
        $this->date = date('Y-m-d');
        $this->time = date('H:i:s');
        $this->namespace = "app\\admin\\controller{$lastNameSpace}";
        $this->controllerDir = $this->rootDir . 'app' . $this->DS . $this->module . $this->DS . 'controller' . $dir;
        $this->viewDir = $this->rootDir . 'app' . $this->DS . $this->module . $this->DS . 'view' . $dir . $this->DS . $transition;
        $this->jsDir = $this->rootDir . 'public' . $this->DS . 'static' . $this->DS . 'admin' . $this->DS . 'js' . $dir;
    }

    /**
     * 执行方法
     */
    public function run()
    {
        $this->renderController();
        $this->renderView();
        $this->renderJs();
    }

    /**
     * 渲染控制器
     */
    private function renderController(): void
    {
        // 读取tpl文件
        $content = $this->read_file(__DIR__ . $this->DS . 'tpl' . $this->DS . 'controller.tpl');
        $content = sprintf($content, $this->date, $this->time, $this->namespace, $this->classname);
        // 写文件
        $this->write_file($this->controllerDir, $this->controllerName, $content);
    }

    /**
     * 渲染html
     */
    private function renderView(): void
    {
        // 读取tpl文件
        $content = $this->read_file(__DIR__ . $this->DS . 'tpl' . $this->DS . 'view.tpl');
        // 写文件
        $this->write_file($this->viewDir, $this->viewName, $content);
    }

    /**
     * 渲染js
     */
    private function renderJs(): void
    {
        // 读取tpl文件
        $content = $this->read_file(__DIR__ . $this->DS . 'tpl' . $this->DS . 'js.tpl');
        // 写文件
        $this->write_file($this->jsDir, $this->jsName, $content);
    }


    /**
     * 处理视图和JS名字
     * @param string $str
     * @return array
     */
    private function handleName(string $str): array
    {
        // 原始名字数组
        $originNameArr = array_values(array_filter(explode($this->DS, $str)));
        // 原始名字数组长度
        $originNameArrCount = count($originNameArr);
        // 文件名转下划线
        $transition = $this->hump2underline($originNameArr[$originNameArrCount - 1]);
        $final_name = '';
        $final_namespace = '';
        $final_classname = '';
        $final_dir = '';
        foreach ($originNameArr as $key => $item) {
            if ($key == $originNameArrCount - 1) {
                $final_classname = $item;
                $final_name .= $this->DS . $transition;
                break;
            }
            $final_name .= $this->DS . $item;
            $final_dir .= $this->DS . $item;
            $final_namespace .= "\\$item";
        }
        return [$final_name, $final_namespace, $final_classname, $final_dir, $transition];
    }

    /**
     * 驼峰形式字符串转成下划线形式
     * @param string $hump_str 驼峰形式字符串
     * @return string
     */
    private function hump2underline(string $hump_str): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . '_' . "$2", $hump_str));
    }

    /**
     * 读文件
     * @param string $file_name 要读取的文件路径/名字
     * @return false|string
     */
    private function read_file(string $file_name)
    {
        $file = fopen($file_name, 'r');
        $file_content = fread($file, filesize($file_name));
        fclose($file);
        return $file_content;
    }

    /**
     * 写文件
     * @param string $base_dir 基础文件夹名字
     * @param string $write_path 要写入的路径/名字
     * @param string $write_content 要写入的内容
     */
    public function write_file(string $base_dir, string $write_path, string $write_content): void
    {
        try {
            if (!file_exists($write_path)) {
                if (!is_dir($base_dir)) {
                    @mkdir($base_dir, 0755, true);
                }
                $file = fopen($write_path, 'w', true);
                fwrite($file, $write_content);
                fclose($file);
            }
        } catch (\Exception $e) {
            halt($e->getMessage());
            $this->print_format('写入文件失败');
        }
    }

    /**
     * 输出样式
     * @param string $text 输出文本
     * @param int $num 间隔符号个数
     * @param string $symbol 间隔符号
     */
    public function print_format(string $text, int $num = 30, string $symbol = '*')
    {
        $output = '';
        for ($i = 0; $i < ($num * 2); $i++) {
            $output .= $symbol;
            if ($i == $num) {
                $output .= $text;
            }
        }
        halt($output);
    }
}