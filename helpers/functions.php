<?php
/**
 * User: wdy
 * Time: 2016.04.21
 */

if (!function_exists('backendView')) {
    /**
     * 展示后台view
     * * @author wdy
     * @param  string $view
     * @param  array $data
     * @param  array $mergeData
     * @return \Illuminate\View\View
     */
    function backendView($view = null, $data = array(), $mergeData = array())
    {
        $factory = app('Illuminate\Contracts\View\Factory');
        if (func_num_args() === 0) {
            return $factory;
        }
        $baseviewPath = config('path.backendBaseViewPath');
        $module = config('path.class');
        if (!empty($module)) {
            $baseviewPath .= config('path.modules.' . $module);
            Config::set('path.class', '');
        }
        $data['module'] = $module;
        $data['data'] = app('request')->all();
        return $factory->make($baseviewPath . $view, $data, $mergeData);
    }
}
if (!function_exists('backendRoute')) {

    function backendRoute($res = null, $data = [], $module = null)
    {
        if (null == $data)
            $data = [];

        $baseviewPath = config('path.backendBaseViewPath');
        $default = '/' . str_replace('.', '', $baseviewPath);

        if (empty($data) && ($res == RES_SHOW || $res == RES_EDIT || $res == RES_DESTROY))
            return $default;

        if (null == $module)
            $module = config('path.class');

        if (preg_match("/.*-(.*)Controller/is", str_replace('\\', '-', $module), $matches))
            $module = strtolower($matches[1]);

        if (!empty($module)) {
            $baseviewPath .= config('path.modules.' . $module) . $res;
            Config::set('path.class', '');
        } else
            return $default;

        return route($baseviewPath, $data);
    }
}
if (!function_exists('conversionClassPath')) {
    /**
     * 转换class 名
     * @author wdy
     * @param  string $className
     * @return string
     */
    function conversionClassPath($className)
    {
        $className = str_replace('\\', '-', $className);
        if (preg_match("/.*-(.*)Controller/is", $className, $matches)) {
            Config::set('path.class', strtolower($matches[1]));
        } else {
            return response('conversionClassPathError', 500);
        }
    }
}
if (!function_exists('getControlName')) {
    /**
     * 获取转换的 Controller 名 (例如:AdminController 转换后 admin)
     * @author wdy
     * @param  string $className
     * @return string
     */
    function getControlName($className)
    {
        $className = str_replace('\\', '-', $className);
        preg_match("/.*-(.*)Controller/is", $className, $matches);
        return strtolower($matches[1]);
    }
}
if (!function_exists('homeView')) {
    /**
     * 展示前台view
     * @author wdy
     * @param  string $view
     * @param  array $data
     * @param  array $mergeData
     * @return \Illuminate\View\View
     */
    function homeView($view = null, $data = array(), $mergeData = array())
    {
        $factory = app('Illuminate\Contracts\View\Factory');
        if (func_num_args() === 0) {
            return $factory;
        }
        $themes = THEMES_NAME . '.' . Config::get('app.themes');
        return $factory->make($themes . '.' . $view, $data, $mergeData);
    }
}
if (!function_exists('homeAsset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string $path
     * @param  bool $secure
     * @return string
     */
    function homeAsset($path, $secure = null)
    {
        $themes = THEMES_NAME . DIRECTORY_SEPARATOR . Config::get('app.themes');
        return app('url')->asset($themes . $path, $secure);
    }
}

if (!function_exists('strCut')) {
    /**
     * 字符串截取
     * @param string $string
     * @param integer $length
     * @param string $suffix
     * @return string
     */
    function strCut($string, $length, $suffix = '...')
    {
        $resultString = '';
        $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
        $strLength = strlen($string);
        for ($i = 0; (($i < $strLength) && ($length > 0)); $i++) {
            if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0')) {
                if ($length < 1.0) {
                    break;
                }
                $resultString .= substr($string, $i, $number);
                $length -= 1.0;
                $i += $number - 1;
            } else {
                $resultString .= substr($string, $i, 1);
                $length -= 0.5;
            }
        }
        $resultString = htmlspecialchars($resultString, ENT_QUOTES, 'UTF-8');
        if ($i < $strLength) {
            $resultString .= $suffix;
        }
        return $resultString;
    }
}

if (!function_exists('viewInit')) {
    /**
     * 设置公共的视图数据
     * @param string $string
     * @param integer $length
     * @param string $suffix
     * @return string
     */
    function viewInit()
    {
        //TODO 公共的视图数据
        $article = app('App\Model\Article');
        $tags = app('App\Model\Tag');
        $view = app('view');
        $nav = app('App\Model\Navigation');
        $links = app('App\Model\Links');


        $view->share('hotArticleList', $article::getHotArticle(3));
        $view->share('tagList', $tags::getHotTags(12));
        $view->share('navList', $nav::getNavigationAll());
        $view->share('linkList', $links::getLinkList());
    }
}

if (!function_exists('uploadFile')) {
    /**
     * @param $type
     * @param $field
     * @param $path
     * @return bool|string
     */
    function uploadFile($type, $field, $path, $limit = 10485760, $newName = '')
    {
        $absolutePath = public_path() . $path;//路径
        $allowType = array(
            'img' => array(
                'image/gif',
                'image/ief',
                'image/jpeg',
                'image/png',
                'image/tiff',
                'image/x-ms-bmp',
            ),
            'audio' => array(
                'audio/x-hx-aac-adts',
            ),
            'app' => array(
                'application/java-archive',
                'application/zip',
            ),
            '*' => '*'
        );
        $url = '0';
        if (!isset($allowType[$type])) {
            return false;
        }
        $request = app('Request');
        if ($request::hasFile($field)) {
            $pic = $request::file($field);
            if ($pic->getSize() > $limit)
                return "";

            if ($type == '*' || in_array($pic->getMimeType(), $allowType[$type])) {
                if ($pic->isValid()) {
                    if (empty($newName))
                        $newName = md5(rand(1, 99999) . $pic->getClientOriginalName()) . "." . $pic->getClientOriginalExtension();
                    else
                        $newName = $newName . "." . $pic->getClientOriginalExtension();
                    $pic->move($absolutePath, $newName);
                    $url = $path . $newName;
                }
            }
        }
        return $url;
    }
}

if (!function_exists('getAdminUser')) {
    /**
     * @return \App\Models\User
     */
    function getAdminUser()
    {
        $obj = app('App\Util\UserObject');
        if (null == $obj::getAdminUser()) {
            $user = new \App\Models\User();
            return $user;
        }

        return $obj::GetAdminUser();
    }
}

if (!function_exists('adminAuth')) {
    /**
     * @return \App\Models\User
     */
    function adminAuth()
    {
        $obj = app('App\Util\UserObject');
        return $obj::AdminAuth();
    }
}

if (!function_exists('getSelectList')) {
    /**
     * @param $name
     * @return mixed
     */
    function getSelectList($name, $unsetIndex = -99)
    {
        $conf = config("selectlist.{$name}");
        if ($unsetIndex != -99)
            unset($conf[$unsetIndex]);
        return $conf;
    }
}

if (!function_exists('setRequest')) {
    /**
     * 设置请求参数
     * @param $name 名称
     * @param $value 值
     */
    function setRequest($name, $value)
    {
        $request = app('request');
        $request->query->set($name, $value);
    }
}

if (!function_exists('getClass')) {

    function getClass($class)
    {
        return str_replace('\\', '.', $class);
    }
}

if (!function_exists('_includeRadio')) {

    /**
     * 自定义radio控件
     *
     * @param $radio_name       name属性
     * @param $radioArr         radio集合
     * @param int $radio_active 默认选中key
     * @param string $type 样式类型
     * @return $this|string
     */
    function _includeRadio($radio_name, $radioArr, $radio_active = 1, $type = 'default')
    {
        if (empty($radio_name) || empty($radioArr))
            return "";

        if ('default' == $type)
            return view('layouts._control._radio')->with(['radio_name' => $radio_name, 'radioArr' => $radioArr, 'radio_active' => $radio_active]);
    }
}

if (!function_exists('_includeArea')) {

    /**
     * 自定义城市选择控件
     *
     * @param string $citys
     * @return $this
     */
    function _includeArea($citys = '', $name = 'city_select')
    {
        return view('layouts._control._area')->with(['citys' => $citys, 'name' => $name]);
    }
}

if (!function_exists('getAreaForControl')) {

    /**
     * 获取地区选择控件值
     * @param string $name
     * @return string
     */
    function getAreaForControl($name = 'city_select')
    {
        return implode(',', request($name));
    }
}

if (!function_exists('configCustom')) {
    /**
     * @param $name
     * @return mixed
     */
    function configCustom($name)
    {
        $conf = config("custom.{$name}");
        return $conf;
    }
}