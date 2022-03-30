<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2021-07-15
 * Time: 21:33
 * Description:
 */

namespace app\common\command;


use app\common\service\builder\FastBuild;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Builder extends Command
{
    protected function configure()
    {
        $this->setName('builder')
            ->addOption('controller', '-c', Option::VALUE_REQUIRED, '控制器名', null)
            ->setDescription('admin模块生成命令');
    }

    protected function execute(Input $input, Output $output)
    {
        $controller = $input->getOption('controller');

        if (!$controller) {
            halt('参数不能为空');
        }
        (new FastBuild($controller))->run();
    }
}