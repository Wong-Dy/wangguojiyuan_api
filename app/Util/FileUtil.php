<?php
/**
 * 目录文件操作类
 * @author  wdy
 * @date    2016-01-11
 */

namespace App\Util;

use ZipArchive;
use Config, Exception;

class FileUtil
{
    const LOG_PATH = DEFAULT_LOG_PATH . "/FileUtil/";

    /**
     * 创建文件
     * @param $file_name
     * @param $file_content
     * @return string
     */
    public static function addFile($file_name, $file_content)
    {
        $dir_name = dirname($file_name);

        //目录不存在就创建
        if (!file_exists($dir_name)) {
            if (static::mk_dirs($dir_name) != "")
                return "目录创建失败";
        }
        if (file_exists($file_name)) {
            @unlink($file_name); //文件存在，就删除
        }

        $cjjer_handle = fopen($file_name, "w+"); //创建文件
        if (!is_writable($file_name)) { //判断写权限
            return '不能写文件';
        }
        if (!fwrite($cjjer_handle, $file_content)) {
            return '写入文件失败';
        }
        fclose($cjjer_handle); //关闭指针
        return "";
    }

    /**
     * 创建目录函数
     * @param $dir
     * @return string
     */
    public static function mk_dirs($dir)
    {
        if (!is_dir($dir)) {
            if (!static::_mk_dir($dir)) {
                return '无法创建目录';
            }
        }
        return "";
    }

    /**
     * 循环创建目录
     * @param $dir
     * @param int $mode
     * @return bool
     */
    private static function _mk_dir($dir, $mode = 0755)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) return true;
        if (!self::_mk_dir(dirname($dir), $mode)) return false;
        return @mkdir($dir, $mode);
    }

    /**
     * 创建Zip压缩文件并返回下载路径
     * @param $dir
     * @param $file_name
     * @return int|string
     */
    public static function createZip($dir, $file_name)
    {
        try {
            $zip = new ZipArchive();
            $zip_dir = public_path() . Config::get('app.ZIPDirName') . $file_name . '.zip';
            $dir_name = dirname($zip_dir);

            //目录不存在就创建
            if (!file_exists($dir_name)) {
                static::mk_dirs($dir_name);
            }

            if ($zip->open($zip_dir, ZipArchive::CREATE) === TRUE) {
                static::addFileToZip($dir, $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
                $zip->close(); //关闭处理的zip文件
                return Config::get('app.ZIPDirName') . $file_name . ".zip";
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return 1;
    }

    /**
     * 添加文件到Zip，内部使用
     * @param $path
     * @param $zip
     */
    private static function addFileToZip($path, $zip)
    {
        $handler = opendir($path); //打开当前文件夹由$path指定。
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if (is_dir($path . $filename)) {// 如果读取的某个对象是文件夹，则递归
                    self::addFileToZip($path . $filename, $zip);
                } else { //将文件加入zip对象
                    $file_info_arr = pathinfo($path . $filename);
                    $zip->addFile($path . $filename, $file_info_arr['basename']);
                }
            }
        }
    }

    /**
     * 获取文件夹大小
     * @param $dir
     * @return int
     */
    public static function getDirSize($dir)
    {
        $dir_name = $dir;

        //目录不存在就创建
        if (!file_exists($dir_name)) {
            if (static::mk_dirs($dir_name) != "")
                return 0;//"目录创建失败";
        }

        $sizeResult = 0;
        $handle = opendir($dir);
        while (false !== ($FolderOrFile = readdir($handle))) {
            if ($FolderOrFile != "." && $FolderOrFile != "..") {
                if (is_dir("$dir/$FolderOrFile")) {
                    $sizeResult += static::getDirSize("$dir/$FolderOrFile");
                } else {
                    $sizeResult += filesize("$dir/$FolderOrFile");
                }
            }
        }
        closedir($handle);
        return $sizeResult;
    }

    /**
     * 单位自动转换函数
     * @param $size
     * @return string
     */
    public static function getRealSize($size)
    {
        $kb = 1024;         // Kilobyte
        $mb = 1024 * $kb;   // Megabyte
        $gb = 1024 * $mb;   // Gigabyte
        $tb = 1024 * $gb;   // Terabyte

        if ($size < $kb) {
            return $size . " B";
        } else if ($size < $mb) {
            return round($size / $kb, 2) . " KB";
        } else if ($size < $gb) {
            return round($size / $mb, 2) . " MB";
        } else if ($size < $tb) {
            return round($size / $gb, 2) . " GB";
        } else {
            return round($size / $tb, 2) . " TB";
        }
    }

    /**
     * 清空指定目录下的文件 (不删除根目录)
     * @param $udir
     * @return int
     */
    public static function dropFiles($udir)
    {
        try {
            $files = glob($udir . '*');
            foreach ($files as $val) {
                if (file_exists($val)) {
                    if ($dir = @opendir($val)) {
                        while (false !== ($filename = readdir($dir))) {
                            if ($filename != "." && $filename != "..") {
                                unlink($val . "/" . $filename);
                            }
                        }
                        closedir($dir);
                    }
                    if (is_file($val)) {
                        unlink($val);
                    } else
                        rmdir($val);
                }
            }

            return 0;   //成功
        } catch (Exception $e) {
            //return $e->getMessage();
            return 1;
        }
    }

    /**
     * 删除指定目录下的文件
     * @param $path
     * @param bool $isAbsolute  是否绝对路径  默认false
     * @return bool
     */
    public static function unlink($path, $isAbsolute = false)
    {
        if (!$isAbsolute)
            $path = iconv('utf-8', 'gbk', public_path() . $path);
        else
            $path = iconv('utf-8', 'gbk', $path);

        if (!file_exists($path))
            return false;
        return unlink($path);
    }

    /**
     * 文件上传
     * @param $Img 上传对象
     * @param string $show 存放目录
     * @return int|string
     */
    public static function uploadFile($Img, $show = '/File/')
    {

        try {

            $show = $show . date('Ymd', time()) . '/';
            $path = public_path() . $show;//路径

            $phtypes = array(
                'text/plain',
                'image/jpg',

            );

            if (!is_dir($path))        //路径若不存在则创建
                mkdir($path);

            $upfile = $Img;
            $pinfo = pathinfo($upfile["name"]);
            $name = $pinfo['basename'];//文件名
            $tmp_name = $upfile["tmp_name"];
            $file_type = $pinfo['extension'];//获得文件类型

            $name = Comm::getGuid() . '.' . $file_type;

            if ($upfile["size"] > 1024000)        //大于1024k
                return 1;

            //限制上传文件类型
            // if(!in_array($upfile["type"],$phtypes))
            //      return 2;

            if (move_uploaded_file($tmp_name, $path . $name))
                return $show . $name;

            return 90;
        } catch (Exception $e) {
            return 99;
        }
    }

    /**
     * APP图片上传
     * @param $Img  上传对象
     * @param string $show  存放目录
     * @return int|string
     */
    public static function appUploadImg($Img, $show = '/AppImage/')
    {
        try {

            $show = $show . date('Ymd', time()) . '/';
            $path = public_path() . $show;//路径

            $phtypes = array(
                'gif',
                'jpg',
                'jpeg',
                'png'
            );

            if (!is_dir($path))        //路径若不存在则创建
                self::mk_dirs($path);

            $upfile = $Img;
            $pinfo = pathinfo($upfile["name"]);

            $name = $pinfo['basename'];//文件名
            $tmp_name = $upfile["tmp_name"];
            $file_type = $pinfo['extension'];//获得文件类型

            $name = Comm::getGuid() . '.' . $file_type;

            if ($upfile["size"] > 1024000)        //大于1024k
                return 1;

            if (!in_array($file_type, $phtypes))
                return 2;

            if (move_uploaded_file($tmp_name, $path . $name)) {
                return $show . $name;
            }
            return 99;
        } catch (Exception $e) {
            Tool::writeLog($e->getMessage(), __METHOD__, self::LOG_PATH);
            return 99;
        }
    }

    /**
     * 生成缩略图函数（支持图片格式：gif、jpg、png和bmp）
     * @param null $src 源图片路径 [/Image/img.jpg]
     * @param int $width    缩略图宽度（只指定高度时进行等比缩放）
     * @param int $height   缩略图高度（只指定宽度时进行等比缩放）
     * @param null $filename    保存路径（不指定时按原文件路径保存）
     * @return bool|string
     */
    public static function thumbnail($src = null, $width = 200, $height = 200, $filename = null)
    {
        if (null == $src)
            return '原图片路径不能为空';

        $src = public_path() . $src;

        if (!isset($width) && !isset($height))
            return false;
        if (isset($width) && $width <= 0)
            return false;
        if (isset($height) && $height <= 0)
            return false;

        if (!file_exists($src))
            return '文件不存在';

        $new_dir = dirname($src);

        $size = getimagesize($src);  //获取图片信息

        if (!$size)
            return false;

        list($src_w, $src_h, $src_type) = $size; //获取图片宽高和类型
        $src_mime = $size['mime'];
        switch ($src_type) {
            case 1 :
                $img_type = 'gif';
                break;
            case 2 :
                $img_type = 'jpeg';
                break;
            case 3 :
                $img_type = 'png';
                break;
            case 15 :
                $img_type = 'wbmp';
                break;
            default :
                return false;
        }

        $name = explode(".", basename($src));
        $new_name = $name[count($name) - 2];

        if (!isset($width))
            $width = $src_w * ($height / $src_h);
        if (!isset($height))
            $height = $src_h * ($width / $src_w);

        $imagecreatefunc = 'imagecreatefrom' . $img_type;
        $src_img = $imagecreatefunc($src);

        $dest_img = imagecreatetruecolor($width, $height);
        imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $width, $height, $src_w, $src_h);

        $imagefunc = 'image' . $img_type;
        $imgUrl = '/' . $new_name . '_T.' . $img_type;
        if ($filename) {  //是否有生成输出地址
            $imagefunc($dest_img, $filename);
        } else {
            $filename = $new_dir . $imgUrl;
            $imagefunc($dest_img, $filename);
        }
        imagedestroy($src_img);
        imagedestroy($dest_img);
        return $imgUrl;
    }

}

