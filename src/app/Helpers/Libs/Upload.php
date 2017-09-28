<?php
namespace app\Helpers\Libs;
use Server\CoreBase\XssClean;
use Server\CoreBase\SwooleException;
/**
 * 文件上传类
 * 限制尺寸，压缩，生成缩略图，限制格式
 * @author Tianfeng.Han
 * @package Swoole
 * @subpackage SwooleSystem
 */
class Upload
{
    public $mimes;
    public $max_size = 10 * 1024 * 1024;
    public $allow = array('jpg', 'gif', 'png', 'jpeg'); //允许上传的类型
    public $name_type = ''; //md5,

    /**
     * 上传文件的根目录
     */
    public $base_dir;

    /**
     * 替换后的域名
     */
    public $base_url = '';

    //指定子目录
    public $sub_dir;

    //子目录生成方法，可以使用randomkey，或者date
    public $shard_type = 'date';
    //子目录生成参数
    public $shard_argv;
    //文件命名法
    public $filename_type = 'randomkey';
    //检查是否存在同名的文件
    public $exist_check = true;
    //允许覆盖文件
    public $overwrite = true;

    /**
     * 限制上传文件的尺寸，如果超过尺寸，则压缩
     */
    public $max_width = 0; //如果为0的话不压缩
    public $max_height;
    public $max_qulitity = 80;

    /**
     * 产生缩略图
     */
    public $thumb_dir;
    public $thumb_width = 0; //如果为0的话不生成缩略图
    public $thumb_height;
    public $thumb_qulitity = 100;

    public $error_msg;
    /**
     * 错误代码
     * 0,不存在的上传 1,未知的mime格式 2,不允许上传的格式
     * 3,文件已存在  4,文件尺寸超过最大
     * @var int
     */
    public $error_code;
    
    private $files;

    function __construct($host_root, $files)
    {
        $_upload_dir = get_instance()->config->get('upload_dir');
        $config['base_dir'] = $host_root. $_upload_dir;
        $config['base_url'] = get_www($_upload_dir);
        if (empty($config['base_dir']) or empty($config['base_url']))
        {
            throw new SwooleException(__CLASS__.' require base_dir and base_url.');
        }
        $this->base_dir = $config['base_dir'];
        if (Tool::endchar($this->base_dir) != '/')
        {
            $this->base_dir .= '/';
        }
        $this->base_url = $config['base_url'];
        $this->mimes = get_instance()->config->get('mimes');
        $this->mimes = array_flip($this->mimes);
        $this->files = $files;
    }

    function error_msg()
    {
        return $this->error_msg;
    }
    function save_all()
    {
        if(!empty($this->files))
		{
			foreach($this->files as $k=>$f)
			{
				if(!empty($this->files[$k]['type'])) {
				    $this->save($k);
				}
			}
		}
    }

    static function moveUploadFile($tmpfile, $newfile)
    {
        return rename($tmpfile, $newfile);
    }

    /**
     * 保存上传的图片
     * @param $name
     * @param null $filename
     * @param null $allow
     * @return bool
     */
    function save($name, $filename = null, $allow = null)
    {
        //检查请求中是否存在上传的文件
        if (empty($this->files[$name]['type']))
        {
            $this->error_msg = "No upload file '$name'!";
            $this->error_code = 0;
            return false;
        }

        //文件存储的路径
        $base_dir = empty($this->sub_dir) ? $this->base_dir : $this->base_dir . $this->sub_dir . '/';
        $base_url = empty($this->sub_dir) ? $this->base_url : $this->base_url. '/' . $this->sub_dir;

        //切分目录
        if ($this->shard_type == 'randomkey')
        {
            if (empty($this->shard_argv))
            {
                $this->shard_argv = 8;
            }
            $sub_dir = RandomKey::randmd5($this->shard_argv);
        }
        elseif ($this->shard_type == 'user')
        {
            $sub_dir = $this->shard_argv;
        }
        else
        {
            if (empty($this->shard_argv))
            {
                $this->shard_argv = 'Ym/d';
            }
            $sub_dir = date($this->shard_argv);
        }

        //上传的最终绝对路径，如果不存在则创建目录
        $path = $base_dir . $sub_dir;
        if (!is_dir($path))
        {
            if (mkdir($path, 0777, true) === false)
            {
                $this->error_msg = "mkdir path=$path fail.";
                return false;
            }
        }

        //过滤危险字符
        $this->files[$name]['name'] = XssClean::getXssClean()->xss_clean($this->files[$name]['name']);

        //MIME格式
        $mime = $this->files[$name]['type'];
        $filetype = $this->getMimeType($mime);
        if ($filetype === 'bin')
        {
            $filetype = self::getFileExt($this->files[$name]['name']);
        }
        if ($filetype === false)
        {
            $this->error_msg = "未知的文件扩展 '$mime'！";
            $this->error_code = 1;
            return false;
        }
        elseif (!in_array($filetype, $this->allow))
        {
            $this->error_msg = " '$filetype' 类型的文件不允许上传！";
            $this->error_code = 2;
            return false;
        }

        //生成文件名
        if ($filename === null)
        {
            $filename = RandomKey::randtime();
            //如果已存在此文件，不断随机直到产生一个不存在的文件名
            while ($this->exist_check and is_file($path . '/' . $filename . '.' . $filetype))
            {
                $filename = RandomKey::randtime();
            }
        }
        elseif ($this->overwrite === false and is_file($path . '/' . $filename . '.' . $filetype))
        {
            $this->error_msg = "'$path/$filename.$filetype' 文件已存在！";
            $this->error_code = 3;
            return false;
        }
        if ($this->shard_type != 'user')
        {
            $filename .= '.' . $filetype;
        }

        //检查文件大小
        $filesize = filesize($this->files[$name]['tmp_name']);
        if ($this->max_size > 0 and $filesize > $this->max_size)
        {
            $this->error_msg = "图片大小不超过".Lib::byteFormat($this->max_size)."，请重传";
            $this->error_code = 4;
            return false;
        }
        $save_filename = $path . "/" . $filename;

    	//写入文件
        if (self::moveUploadFile($this->files[$name]['tmp_name'], $save_filename))
        {
            //产生缩略图
            if ($this->thumb_width and in_array($filetype, array('gif', 'jpg', 'jpeg', 'bmp', 'png')))
            {
                $thumb_file = str_replace('.' . $filetype,
                    '_' . $this->thumb_width . 'x' . $this->thumb_height . '.' . $filetype,
                    $filename);
                Image::thumbnail($save_filename,
                    $path . '/' . $thumb_file,
                    $this->thumb_width,
                    $this->thumb_height,
                    $this->thumb_qulitity);
                $return['thumb'] =  "{$base_url}/{$sub_dir}/{$thumb_file}";
            }
            //压缩图片
            if ($this->max_width and in_array($filetype, array('gif', 'jpg', 'jpeg', 'bmp', 'png')))
            {
                Image::thumbnail($save_filename,
                    $save_filename,
                    $this->max_width,
                    $this->max_height,
                    $this->max_qulitity);
            }
            $return['url'] = "{$base_url}/{$sub_dir}/{$filename}";
            $return['size'] = $filesize;
            $return['type'] = $filetype;
            return $return;
        }
        else
        {
            $this->error_msg = "move upload file fail. tmp_name={$this->files[$name]['tmp_name']}|dest_name={$save_filename}";
            $this->error_code = 2;
            return false;
        }
    }
    /**
     * 获取MIME对应的扩展名
     * @param $mime
     * @return bool
     */
    public function getMimeType($mime)
    {
        if (isset($this->mimes[$mime]))
        {
            return $this->mimes[$mime];
        }
        else
        {
            return false;
        }
    }

    /**
     * 根据文件名获取扩展名
     * @param $file
     * @return string
     */
    static public function getFileExt($file)
    {
        $s = strrchr($file, '.');
        if ($s === false)
        {
            return false;
        }
        return strtolower(trim(substr($s, 1)));
    }
}