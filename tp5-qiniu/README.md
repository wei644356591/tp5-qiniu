qiniu-tp5

基于tp5框架的七牛云存储实现，可以方便的实现文件上传，文件管理功能。只需两行代码就能完成一次文件上传
安装方式。使用composer安装，在项目根目录下运行

composer require gmars/tp5-qiniu

如果该方法安装不成功，请在项目根目录下的composer.json的require中添加

"gmars/tp5-qiniu": "dev-master"

然后使用cmd进入项目根目录下运行composer update

使用方式：

一、配置使用

1.1配置：

在tp5的配置文件config.php中配置七牛云的配置参数，当然此插件支持实例化时再传入配置参数

'qiniu' => [

        'accesskey' => '你自己的七牛云accesskey',
        'secretkey' => '你自己的七牛云secretkey',
        'bucket' => '你自己创建的bucket',
 ]

1.2使用

 try{
 
      $qiniu = new Qiniu();
      $result = $qiniu->upload();
      
 }catch (Exception $e){
 
      dump($e);
 }
 
 如果上传成功则返回的是key值也就是文件对应的key使用你自己的域名拼接key就可以直接访问了</p>


二、直接使用

  try{
  
      $qiniu = new Qiniu('你自己的七牛云accesskey','你自己的七牛云secretkey','你自己创建的bucket');
      $result = $qiniu->upload();
      
 }catch (Exception $e){
 
      dump($e);
 }




如果使用中有任何错误或者疑问可以给我发邮件：secret_01@foxmail.com

