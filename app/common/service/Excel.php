<?php
/**
 * Created by PhpStorm.
 * User: Kassy
 * Date: 2022-03-17
 * Time: 13:17
 * Description:
 */

namespace app\common\service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use think\facade\Request;

class Excel
{
    /**
     * 导入
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function import(): array
    {
        $file = Request::file('excel');
        $ext = $file->extension();
        $dirname = public_path() . "media/excel/" . date('Ymd');
        $fileName = md5(microtime(true)) . ".$ext";
        $file->move($dirname, $fileName);

        $reader = IOFactory::createReader(ucfirst($ext));
        $reader->setReadDataOnly(true);
        $objWork = $reader->load($dirname . '/' . $fileName);
        $data = $objWork->getSheet(0)->toArray();
        // 删除第一个数组(标题)
        array_shift($data);

        unlink($dirname . '/' . $fileName);
        return $data;
    }
}