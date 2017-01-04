<?php
/**
 * 文件缓存，目前不支持分布式
 * @author weihan
 * @datetime 2016年11月23日下午3:40:34
 */
namespace Server\Cache;


class FileCache implements ICache{
    
    /*缓存默认配置*/
    protected $setting = array(
        'suf' => '.cache.php',	//缓存文件后缀
        'cache_path' => '',     //缓存路径
        'default_ttl' => 3600,  //默认有效期，单位秒
    );
    
    public function __construct($setting) {
        $this->setting = array_merge($this->setting, $setting);
        $this->setting['cache_path'] = SRC_DIR. trim($this->setting['cache_path'], DIRECTORY_SEPARATOR). DIRECTORY_SEPARATOR;
    }
    
    /**
     * 文件路径
     * @param string $key
     *
     * @author weihan
     * @datetime 2016年11月23日下午4:55:32
     */
    private function _getFileFullName($key){
        $md5 = md5($key);
        $sub_dir = substr($md5, 0, 2);
        $filename = $this->setting['cache_path']. $sub_dir. DIRECTORY_SEPARATOR. $key. $this->setting['suf'];
        return $filename;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Server\Cache\ICache::set()
     * 
     * @author weihan
     * @datetime 2016年11月23日下午4:59:26
     */
    public function set($key, $value, $ttl=0){
        $value = serialize($value);
        $filename = $this->_getFileFullName($key);
        //创建目录
        mkdirs(dirname($filename));
        file_put_contents($filename, $value);
        //设置过期时间
        $ttl == 0 && $ttl = $this->setting['default_ttl'];
        $expire = time()+ $ttl;
        return touch($filename, $expire);
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Server\Cache\ICache::get()
     * 
     * @author weihan
     * @datetime 2016年11月23日下午4:59:32
     */
    public function get($key){
        $filename = $this->_getFileFullName($key);
        //加yield是为了和其他缓存类型保持一致
        yield $filename;
        if(file_exists($filename)){
            if(($time=@filemtime($filename))>time()){
                $content = unserialize(file_get_contents($filename));
                return $content;
            }else if($time>0){
                @unlink($filename);
            }
        }
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \Server\Cache\ICache::delete()
     * 
     * @author weihan
     * @datetime 2016年11月23日下午4:59:37
     */
    public function delete($key){
        $filename = $this->_getFileFullName($key);
        if (file_exists($filename)){
            @unlink($filename);
        }
    }
}