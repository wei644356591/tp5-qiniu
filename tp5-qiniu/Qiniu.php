<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/25
 * Time: 17:14
 */

namespace gmars\qiniu;


use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Cache;
use think\Config;
use think\Exception;
use think\Request;

require 'qiniu_driver/autoload.php';

class Qiniu {
    private $_accessKey;
    private $_secretKey;
    private $_bucket;

    private $_error;

    /**
     * Qiniu constructor.
     * @param string $accessKey
     * @param string $secretKey
     * @param string $bucketName
     * 初始化参数可以直接配置到tp的配置中
     */
    public function __construct($accessKey = "", $secretKey = "", $bucketName = "") {
        if (empty($accessKey) || empty($secretKey) || empty($bucketName)) {
            $qiniuConfig = Config::get('qiniu');
            if (empty($qiniuConfig['accesskey']) || empty($qiniuConfig['secretkey'])) {
                $this->_error = '你的配置信息不完整！';
                return false;
            }
            $this->_accessKey = $qiniuConfig['accesskey'];
            $this->_secretKey = $qiniuConfig['secretkey'];
        } else {
            $this->_accessKey = $accessKey;
            $this->_secretKey = $secretKey;
        }

        if (!empty($bucketName)) {
            $this->_bucket = $bucketName;
        }
    }

    /**
     * @return bool|string
     * 获取bucket
     */
    private function _getBucket() {
        if (!empty($this->_bucket)) {
            return $this->_bucket;
        }
        $bucket = Config::get('qiniu.bucket');
        if (empty($bucket)) {
            return false;
        }
        return $bucket;
    }

    /**
     * @param string $saveName
     * @param string $bucket
     * @return mixed
     * @throws Exception
     * @throws \Exception
     * 单文件上传，如果添加多个文件则只上传第一个
     */
    public function upload($name = null,$saveName = null, $bucket = null) {
        $result = new \stdClass();
        $result->status = false;
        $tp5 = Request::instance();
        $token = $this->_getUploadToken($bucket);
        $files = $tp5->file($name);
        if ($files->getError() !=null ){
            $result->error_msg = $files->getError();
            return $result;
        }
        $filename =  $files->getRealPath();
        if (!$filename) {
            $result->error_msg = '没有文件被上传';
            return $result;
        }
        $uploadManager = new UploadManager();
        $ext = strrchr($_FILES[$name]['name'], '.');
        if (!$ext){
            $ext = '.jpg';
        }
        if (!$saveName) {
            $saveName = time().rand(10000,99999).$ext;
        }
        list($ret, $err) = $uploadManager->putFile($token, $saveName, $filename);
        if ($err !== null) {
            $result->error_msg = '文件上传出错';
            return $result;
        }
        $qny_base_url = \config('qiniu.baseurl');
        $result->status = true;
        $result->url = $qny_base_url.$ret['key'];
        return $result;
    }

    /**
     * @param $bucketName
     * @return mixed|string
     * @throws Exception
     * 只有设置到配置的bucket才会使用缓存功能
     */
    private function _getUploadToken($bucketName) {
            $auth = new Auth($this->_accessKey, $this->_secretKey);
            $bucket = empty($bucketName) ? $this->_getBucket() : $bucketName;
            if ($bucket === false) {
                throw new Exception('你还没有设置或者传入bucket', 100001);
            }
            $upToken = $auth->uploadToken($bucket);
            return $upToken;
        }
}