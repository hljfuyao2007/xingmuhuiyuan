<?php
// 应用公共文件

if (!function_exists('datetime')) {
    /**
     * 将时间戳转换为日期时间
     * @param int|string $time 时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, string $format = 'Y-m-d H:i:s'): string
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }
}

if (!function_exists('getRouteList')) {
    /**
     * 获取应用下已注册的所有路由
     * @param string|null $dir
     * @return array
     */
    function getRouteList(string $dir = null): array
    {
        if ($dir) {
            $path = app()->getRootPath() . 'route' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;
        } else {
            $path = app()->getRootPath() . 'route' . DIRECTORY_SEPARATOR;
        }

        $files = is_dir($path) ? scandir($path) : [];
        foreach ($files as $file) {
            if (strpos($file, '.php')) {
                include $path . $file;
            }
        }

        // 触发路由载入完成事件
        app()->event->trigger(\think\event\RouteLoaded::class);

        return app()->route->getRuleList();
    }
}

if (!function_exists('encrypt')) {
    /**
     * 密码加密
     * @param string $password
     * @return string
     */
    function encrypt(string $password): string
    {
        // 盐值
        $salt = env('admin.salt');
        return $password ? md5(sha1($password) . $salt) : '';
    }
}

if (!function_exists('buildMenuChild')) {

    /**
     * 递归获取子菜单
     * @param $menuList
     * @param $pid
     * @return array
     */
    function buildMenuChild($menuList, $pid): array
    {
        $treeList = [];
        foreach ($menuList as $item) {
            if ($pid == $item['pid']) {
                $node = $item;
                $child = buildMenuChild($menuList, $item['id']);
                if (!empty($child)) {
                    $node['child'] = $child;
                }
                $treeList[] = $node;
            }
        }

        return $treeList;
    }
}


if (!function_exists('write_ini_file')) {
    /**
     * 写ini文件
     * @param null|string $mode ini节名
     * @param string $key 键名key
     * @param null $value 键值
     * @param string $filename 文件名
     * @return bool|mixed|null
     */
    function write_ini_file($mode, string $key, $value = null, string $filename = '')
    {
        if (!file_exists($filename)) return false;
        //读取文件
        $iniArr = parse_ini_file($filename, true);
        // 更新后的ini文件内容
        $newIni = "";
        if ($mode != null) {
            //节名不为空
            if ($value === null) {
                return @$iniArr[$mode][$key] === null ? null : $iniArr[$mode][$key];
            } else {
                $YNedit = @$iniArr[$mode][$key] === $value ? false : true;//若传入的值和原来的一样，则不更改
                @$iniArr[$mode][$key] = $value;
            }
        } else {
            //节名为空
            if ($value === null) {
                return @$iniArr[$key] === null ? null : $iniArr[$key];
            } else {
                $YNedit = @$iniArr[$key] === $value ? false : true;//若传入的值和原来的一样，则不更改
                @$iniArr[$key] = $value;
            }

        }
        if (!$YNedit) return true;
        //更改
        $keys = array_keys($iniArr);
        $num = 0;
        foreach ($iniArr as $k => $v) {
            if (!is_array($v)) {
                $newIni = $newIni . $keys[$num] . "=" . $v . "\r\n";
                $num += 1;
            } else {
                $newIni = $newIni . '[' . $keys[$num] . "]\r\n";//节名
                $num += 1;
                $jieM = array_keys($v);
                $jieS = 0;
                foreach ($v as $k2 => $v2) {
                    $newIni = $newIni . $jieM[$jieS] . "=" . $v2 . "\r\n";
                    $jieS += 1;
                }
            }
        }

        if (($fi = fopen($filename, "w"))) {
            flock($fi, LOCK_EX);//排它锁
            fwrite($fi, $newIni);
            flock($fi, LOCK_UN);
            fclose($fi);
            return true;
        }
        return false;//写文件失败
    }
}

if (!function_exists('letter_avatar')) {
    /**
     * 首字母头像
     * @param string $text
     * @return string
     */
    function letter_avatar(string $text): string
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" alignment-baseline="central">' . $first . '</text></svg>');
        return 'data:image/svg+xml;base64,' . $src;
    }
}

if (!function_exists('hsv2rgb')) {
    /**
     * 字母头像换色
     * @param $h
     * @param $s
     * @param $v
     * @return array
     */
    function hsv2rgb($h, $s, $v): array
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}

if (!function_exists('sysconfig')) {
    /**
     * 获取系统配置信息
     * @param $group
     * @param null $name
     * @return array|mixed|object|\think\App
     */
    function sysconfig($group, $name = null)
    {
        $where = [
            ['group', '=', $group]
        ];
        $value = is_null($name) ? cache("sysconfig_{$group}") : cache("sysconfig_{$group}_{$name}");
        if (!$value) {
            if ($name) {
                $where[] = ['name', '=', $name];
                $value = \app\admin\model\SystemConfig::where($where)->value('value', '');
                \think\facade\Cache::tag('sysconfig')->set("sysconfig_{$group}_{$name}", $value, 3600);
            } else {
                $value = \app\admin\model\SystemConfig::where($where)->column('value', 'name');
                \think\facade\Cache::tag('sysconfig')->set("sysconfig_{$group}", $value, 3600);
            }
        }
        return $value;
    }
}

if (!function_exists('filePathJoin')) {
    /**
     * 拼接文件路径
     * @param string $url
     * @param string $type
     * @return string
     */
    function filePathJoin(string $url = '', string $type = ''): string
    {
        if (!$url) {
            return $url;
        }

        $type = $type ?: sysconfig('upload', 'upload_type');

        switch ($type) {
            case 'alioss':
                $style = explode(',', sysconfig('upload')['alioss_style'])[0] ?? '';
                $url = sysconfig('upload')['alioss_domain'] . $url . $style;
                break;
            case 'qnoss':
            case 'txcos':
                break;
            default:
                $url = request()->domain() . $url;
        }

        return $url;
    }
}

if (!function_exists('lineToHump')) {
    /**
     * 下划线转驼峰
     * @param $value
     * @return array|string|string[]
     */
    function lineToHump($value): string
    {
        return preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $value);
    }
}

if (!function_exists('humpToLine')) {
    /**
     * 驼峰转下划线
     * @param $value
     * @return array|string|string[]|null
     */
    function humpToLine($value): string
    {
        return preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $value);
    }
}

if (!function_exists('apiShow')) {
    /**
     * 接口输出格式
     * @param mixed $data 数据
     * @param string $msg 提示信息
     * @param int $code 状态码
     * @param bool $is_show_time 是否显示当前时间
     * @param bool $is_json 是否json输入
     * @return array|\think\response\Json
     */
    function apiShow($data = [], string $msg = 'success', int $code = 0, bool $is_show_time = true,
                     bool $is_json = true)
    {
        if ($code == 0) {
            $result = [
                'code' => $code,
                'msg'  => $msg,
                'data' => $data,
            ];
        } elseif ($code == 1) {
            $result = [
                'code' => $code,
                'msg'  => $msg
            ];
        } elseif ($code < 0) {
            $result = [
                'code' => $code,
                'msg'  => $msg
            ];
        } else {
            $result = [
                'code' => $code,
                'msg'  => $msg,
                'data' => $data
            ];
        }
        if ($is_show_time) {
            $result['curtime'] = time();
        }

        return $is_json ? json($result) : $result;
    }
}

if (!function_exists('getRealIp')) {
    /**
     * 获取真实IP
     * @return mixed
     */
    function getRealIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] as $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
        return $ip;
    }
}

if (!function_exists('readDirAllFiles')) {
    /**
     * 读取文件夹下的所有文件
     * @param string $path
     * @param string $basePath
     * @return array
     */
    function readDirAllFiles(string $path, string $basePath = ''): array
    {
        list($list, $temp_list) = [[], scandir($path)];
        empty($basePath) && $basePath = $path;
        foreach ($temp_list as $file) {
            if ($file != ".." && $file != ".") {
                if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                    $childFiles = readDirAllFiles($path . DIRECTORY_SEPARATOR . $file, $basePath);
                    $list = array_merge($childFiles, $list);
                } else {
                    $filePath = $path . DIRECTORY_SEPARATOR . $file;
                    $fileName = str_replace($basePath . DIRECTORY_SEPARATOR, '', $filePath);
                    $list[$fileName] = $filePath;
                }
            }
        }
        return $list;
    }
}

if (!function_exists('auth')) {
    /**
     * 验证权限
     * @param string $url 要验证的url
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function auth(string $url): bool
    {
        return \app\common\service\AuthCore::getInstance()->verifyAuth($url);
    }
}

if (!function_exists('find_level')) {
    /**
     * 递归level
     * @param $data
     * @param $major_key
     * @param int $parent_id
     * @param int $level
     * @return array
     */
    function find_level($data, $major_key, int $parent_id = 0, int $level = 1): array
    {
        $information = [];
        foreach ($data as $item) {
            if ($item['pid'] == $parent_id) {
                $item['level'] = $level;
                $information[] = $item;
                $child = find_level($data, $major_key, $item[$major_key], $level + 1);
                if (is_array($child)) {
                    $information = array_merge($information, $child);
                }
            }
        }
        return $information;
    }
}

if (!function_exists('make_ico')) {
    /**
     * 生成ico图标
     * @param string $image_tmp
     */
    function make_ico(string $image_tmp)
    {
        $ext = pathinfo($image_tmp, PATHINFO_EXTENSION);
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $image = imagecreatefromjpeg(filePathJoin($image_tmp));
        } else {
            $image = imagecreatefrompng(filePathJoin($image_tmp));
        }

        list($width, $height) = getimagesize(filePathJoin($image_tmp));

        $newWidth = 64;
        $newHeight = 64;

        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        //将图像复制到具有新宽度和高度的图像
        imagecopyresampled($tmp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $tmp_path = root_path() . 'public/static/admin/images/favicon.ico';
        imagejpeg($tmp, $tmp_path, 100) or die('没有创建文件的权限');
        imagedestroy($tmp);
    }
}

if (!function_exists('rand_string')) {
    /**
     * 随机产生数字与字母混合且小写的字符串(唯一)
     * @param int $len 数值长度,默认32位
     * @param bool $lower 是否小写,否则大写
     * @return string
     */
    function rand_string(int $len = 32, bool $lower = true): string
    {
        $string = mb_substr(md5(uniqid(rand(), true)), 0, $len, 'utf-8');
        return $lower ? $string : mb_strtoupper($string, 'utf-8');
    }
}


if (!function_exists('generateInviteCode')) {
    /**
     * 生成不重复的分销邀请码
     * @return string
     */
    function generateInviteCode(): string
    {
        $code = rand_string(6, false);

        \app\common\model\Member::where('invite_code', $code)
            ->value('member_id', '') && generateInviteCode();

        return $code;
    }
}