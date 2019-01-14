<?php
namespace app\index\controller;

use app\index\model\UploadModel;

class Upload
{
    private $filepath = './upload'; //上传目录
    private $tmpPath;  //PHP文件临时目录
    private $blobNum; //第几个文件块
    private $totalBlobNum; //文件块总数
    private $fileName; //文件名

/*    public function __construct($tmpPath,$blobNum,$totalBlobNum,$fileName){
        $this->tmpPath =  $tmpPath;
        $this->blobNum =  $blobNum;
        $this->totalBlobNum =  $totalBlobNum;
        $this->fileName =  $fileName;
        
        $this->moveFile();
        $this->fileMerge();
    }*/

	//调用$ossClient->initiateMultipartUpload方法返回OSS创建的全局唯一的uploadId。
    public function initiateMultipartUpload()
    {
    	//创建文件夹
    	// $this->touchDir();
    	//开辟数据库
    	// $uploadModel = new UploadModel();
    	//开辟缓存
    	exit('ok');
    	// ajaxReturn
 		return return_result(200,'success',$uploadId);
    }

    //调用$ossClient->uploadPart方法上传分片数据。
    //对于同一个uploadId，分片号（partNumber）标识了该分片在整个文件内的相对位置。如果使用同一个分片号上传了新的数据，那么OSS上这个分片已有的数据将会被覆盖。
	//除了最后一块Part以外，其他的Part最小为100KB。最后一块Part没有大小限制。
	//上传Part时，客户端除了需要记录Part号码外，还需要记录每次上传Part成功后，服务器返回的ETag值。
    public function uploadPart($value='')
    {
    	# code...
    }

    //调用$ossClient->completeMultipartUpload方法将所有分片合并成完整的文件。
    // 在执行该操作时，需要提供所有有效的$uploadParts。OSS收到提交的$uploadParts后，会逐一验证每个分片的有效性。当所有的数据分片验证通过后，OSS将把这些分片组合成一个完整的文件。
    public function completeMultipartUpload($value='')
    {
    	# code...
    }

    //您可以调用$ossClient->abortMultipartUpload方法来取消分片上传事件。当一个分片上传事件被取消后，无法再使用这个uploadId做任何操作，已经上传的分片数据会被删除。
    public function abortMultipartUpload($value='')
    {
    	# code...
    }

    //举已上传的分片
    public function listParts($value='')
    {
    	# code...
    }

    //列举分片上传事件
    public function listMultipartUploads($value='')
    {
    	# code...
    }

    //建立上传文件夹
    private function touchDir(){
        if(!file_exists($this->filepath)){
            return mkdir($this->filepath);
        }
    }
}
