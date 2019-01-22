<?php 
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
define('FILE_UPLOAD_PATH', __DIR__ . '/upload/');

define('UPLOAD_UNFINISHED',0);
define('UPLOAD_FINISH_NO_INFO',1);
define('UPLOAD_WAIT_VERIFY',2);
define('UPLOAD_VERIFY_FINISH',3);
//最大2G
define('MAX_UPLOAD_FILE_SIZE',2147483648);

 ?>