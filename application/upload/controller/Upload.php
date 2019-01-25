<?php
namespace app\upload\controller;

use app\upload\model\FileUpload;
use app\upload\model\FileUploadParts;
use think\controller\Rest;
use think\Validate;

class Upload extends Rest
{
    private $filePath; //文件存储目录
    private $partFilePath; //分块文件存储目录
    // private $blobNum; //第几个文件块
    private $uploadInfo; //上传文件信息

    public function __construct($blobNum='',$totalBlobNum=''){
        parent::__construct();
        //校验uploadId有效性
        $this->checkAuth();
    }

    //添加视频信息
    public function addVideoInfo($value='')
    {
        $params = input('post.');
        $validate = new Validate([
            'uploadId'          => 'require|integer',
            'title'             => 'require',
            'description'       => 'require',
            'tags'              => 'require',
        ]);

        $validate->message([
            'uploadId.require'          => '初始化id不能为空!',
            'uploadId.integer'          => '初始化id必须是整数!',
            'title.require'             => '视频标题不能为空!',
            'description.require'       => '视频描述不能为空!',
            'tags.require'              => '视频标签不能为空!',
        ]);

        if (!$validate->check($params)) {
            $this->error($validate->getError());
        }

        //校验是否上传成功
        if ($this->uploadInfo['status'] >= UPLOAD_WAIT_VERIFY) {
            $this->error('已经添加过视频信息');
        }

        $data['id']          = $params['uploadId'];
        $data['title']       = $params['title'];
        $data['description'] = $params['description'];
        $data['tags']        = $params['tags'];
        $data['stars']       = input('post.stars/s','');;
        $data['status']      = UPLOAD_WAIT_VERIFY;
        $uploadModel = new FileUpload();
        $result = $uploadModel->editVideoInfo($data);

        $this->success();
    }

	//调用$ossClient->initiateMultipartUpload方法返回OSS创建的全局唯一的uploadId。
    public function initiateMultipartUpload()
    {
        $params = input('post.');
        $validate = new Validate([
            'fileName'          => 'require',
            'totalParts'        => 'require|integer',
            'totalSize'         => 'require|integer|between:1,'.MAX_UPLOAD_FILE_SIZE,
        ]);

        $validate->message([
            'fileName.require'          => '上传文件名不能为空!',
            'totalParts.require'          => '分片数量不能为空!',
            'totalParts.integer'          => '分片数量必须是整数!',
            'totalSize.require'          => '总大小不能为空!',
            'totalSize.integer'          => '总字节数必须是整数!',
            'totalSize.between'          => '文件不能超过2G!',
        ]);

        if (!$validate->check($params)) {
            $this->error($validate->getError());
        }

        $ext = pathinfo($params['fileName'],PATHINFO_EXTENSION);
        if (empty($ext) ){
            $this->error('请填写正确的文件名');
        }

    	//开辟数据库
    	$uploadModel = new FileUpload();

        $data['user_id']    = $this->userId;
        $data['file_name']  = $params['fileName'];
        $uploadInfo = $uploadModel->getUploadInfo($data);
        // var_dump($uploadInfo);exit();
        if (!empty($uploadInfo) ) {
            if ($uploadInfo['status'] >= UPLOAD_FINISH_NO_INFO ) {
                $this->error('已经上传成功，不能重复上传！');
            }
            $responseData = ['uploadId'=>$uploadInfo['id'] ];
            $partFilePath = $uploadInfo['file_path'] . $uploadInfo['id'] . '/';
            $this->touchDir($partFilePath);
   
            $this->success($responseData);
        }

        $fileNameNew = md5( time().'_'.$this->userId.'_'.mt_rand(6,12).'_'.$params['fileName']).'.'.$ext;
        $filePath = FILE_UPLOAD_PATH . date("Ymd") . '/';
        // var_dump( $fileNameNew );exit();
        $data['total_parts']  = $params['totalParts'];
        $data['total_size']   = $params['totalSize'];
        $data['create_time']  = time();
        $data['status']       = UPLOAD_UNFINISHED;
        $data['file_name_new']    = $fileNameNew;
        $data['file_path']    = $filePath;
        // var_dump($data );exit();
        $result = $uploadModel->addUpload($data);
    	//开辟缓存
        if ($result) {
            //创建文件夹
            $partFilePath = $filePath . $uploadModel->getLastInsID() . '/';
            $this->touchDir($partFilePath);

            $responseData = ['uploadId'=>$uploadModel->getLastInsID()];
            $this->success($responseData);
        }else{
            $this->error('上传失败');
        }

    }

    //调用$ossClient->uploadPart方法上传分片数据。
    //对于同一个uploadId，分片号（partNumber）标识了该分片在整个文件内的相对位置。如果使用同一个分片号上传了新的数据，那么OSS上这个分片已有的数据将会被覆盖。
	//除了最后一块Part以外，其他的Part最小为100KB。最后一块Part没有大小限制。
	//上传Part时，客户端除了需要记录Part号码外，还需要记录每次上传Part成功后，服务器返回的ETag值。
    public function uploadPart($value='')
    {
        $params = input('post.');
        $validate = new Validate([
            'uploadId'          => 'require|integer',
            'partNumber'          => 'require|integer',
        ]);

        $validate->message([
            'uploadId.require'          => '上传id不能为空!',
            'partNumber.require'          => '分片标识不能为空!',
        ]);

        if (!$validate->check($params)) {
            $this->error($validate->getError());
        }
        
        $uploadId = $params['uploadId'];
        $partNumber = $params['partNumber'];
        if (empty($_FILES['file']['tmp_name']) ) {
            $this->error('上传分片不能为空');
        }

       $tmpPath = $_FILES['file']['tmp_name'];

        $partFileName = $this->partFilePath . $this->uploadInfo['file_name_new'].'__'.$partNumber;

        $fileByte = filesize($tmpPath);
        move_uploaded_file($tmpPath, $partFileName);

        $data['upload_id'] = $uploadId;
        $data['part_number']   = $partNumber;
        $data['part_size']   = $fileByte;
        $data['part_etag']   = md5_file($partFileName);
        $data['create_time']   = time();
        $data['status']   = UPLOAD_UNFINISHED;

        $fileUploadParts = new FileUploadParts();
        $checkResult = $fileUploadParts->checkUploadpart($data);
        if ($checkResult) {
            $result = $fileUploadParts->editUploadpart($data);
        }else{
            $result = $fileUploadParts->addUploadpart($data);
        }
        if (empty( $result) ) {
            $this->error('分片上传失败');
        }

        $responseData['ETag']       = $data['part_etag'];
        $responseData['partSize']   = $fileByte;
        $responseData['partNumber'] = $partNumber;
        // $responseData['uploadId']   = $uploadId;
        $this->success($responseData);
    }

    //调用$ossClient->completeMultipartUpload方法将所有分片合并成完整的文件。
    // 在执行该操作时，需要提供所有有效的$uploadParts。OSS收到提交的$uploadParts后，会逐一验证每个分片的有效性。当所有的数据分片验证通过后，OSS将把这些分片组合成一个完整的文件。
    public function completeMultipartUpload($value='')
    {
        $params = input('post.');
        $validate = new Validate([
            'uploadId'          => 'require|integer',
            // 'partList'          => 'require',
        ]);

        $validate->message([
            'uploadId.require'          => '上传id不能为空!',
            // 'partList.require'          => '分片列表不能为空!',
        ]);

        if (!$validate->check($params)) {
            $this->error($validate->getError());
        }

        //校验上传信息
        if ($this->uploadInfo['status'] >= UPLOAD_FINISH_NO_INFO ) {
            $this->error('已经合并过');
        }

        //校验列表
        $fileUploadParts = new FileUploadParts();
        $finishPartInfo  = $fileUploadParts->getPartsInfo($this->uploadInfo['id']);
        $finishTotalParts = $finishPartInfo['totalParts'];
        $totalSize = $finishPartInfo['totalSize'];

        $finishPartList  = $fileUploadParts->checkPartList($this->uploadInfo['id']);
        $finishPartNumber = array_keys($finishPartList);

        $missParts = find_miss($finishPartNumber, $this->uploadInfo['total_parts']);

        if (!empty($missParts) ) {
            $responseData = ['missParts' => $missParts];
            $this->error('分片数量缺少', $responseData);
        }else if ($finishTotalParts != $this->uploadInfo['total_parts']) {
            $responseData = ['finishParts' => $finishTotalParts, 'totalParts'=> $this->uploadInfo['total_parts'] ];
           $this->error('分片数量不对', $responseData);
       }else if($totalSize != $this->uploadInfo['total_size']) {
            $responseData = ['finishSize' => $totalSize, 'totalSize'=> $this->uploadInfo['total_size'] ];
           $this->error('分片大小不对', $responseData);
       }
// var_dump($totalSize, $this->uploadInfo['total_size']);exit();
       //合并
       // $this->filePath =  FILE_UPLOAD_PATH . date("Ymd") .'/'. $params['uploadId'] . '/';
       // $this->fileName = $this->uploadInfo['file_name_new'];


       // if(!file_exists($this->filePath) )
       // {
       //    $this->error('请先初始化');
       // }
       $fileName = $this->filePath . $this->uploadInfo['file_name_new'];
       try {
           $blob = '';
           foreach ($finishPartNumber as $key => $number) {
                $partFileName = $this->partFilePath . $this->uploadInfo['file_name_new'].'__'.$number;
                $blob .= file_get_contents($partFileName);
           }
           file_put_contents( $fileName, $blob);
       } catch (\Exception $e) {
           $this->error('文件合成失败');
       }
       // var_dump( $fileName );exit();
       //删除合并后的分片
       $uploadModel = new FileUpload();
       $uploadModel->changeUploadStatus($this->uploadInfo['id']);
       del_dir($this->partFilePath);
       $delFileParts  = $fileUploadParts->delFileParts($this->uploadInfo['id']);
       $this->success();

    }

    public function checkAuth($value='')
    {
        $uploadId = input('post.uploadId/d');
        if (empty($uploadId) ) {
            return false;
        }
        $uploadModel = new FileUpload();
        $checkId = ['id'=>$uploadId];
        $uploadInfo = $uploadModel->getUploadInfo($checkId);
        if (empty($uploadInfo) ) {
            $this->error('无效的uplaodId');
        }elseif ($uploadInfo['user_id'] != $this->userId) {
            $this->error('用户与当前uploadId不对应');
        }

        $this->filePath = $uploadInfo['file_path'];
        $this->partFilePath = $uploadInfo['file_path'].$uploadInfo['id'].'/';

        if(!file_exists($this->filePath) )
        {
           $this->error('上传错误,请先初始化');
        }

        $this->uploadInfo = $uploadInfo;
    }

    //您可以调用$ossClient->abortMultipartUpload方法来取消分片上传事件。当一个分片上传事件被取消后，无法再使用这个uploadId做任何操作，已经上传的分片数据会被删除。
    public function abortMultipartUpload($value='')
    {
        $params = input('post.');
        // var_dump($this->userId );exit();
        $validate = new Validate([
            'uploadId'          => 'require|integer',
        ]);

        $validate->message([
            'uploadId.require'          => '上传id不能为空!',
        ]);

        if (!$validate->check($params)) {
            $this->error($validate->getError());
        }
       
        //删除分片文件夹
        del_dir($this->partFilePath);
        //上传完成不能删除
        
        $uploadModel = new FileUpload();
        $uploadModel->delUploadId($this->uploadInfo['id']);

        $fileUploadParts = new FileUploadParts();
        $fileUploadParts->delFileParts($this->uploadInfo['id']);

        $this->success();
    }

    //举已上传的分片
    public function listParts($value='')
    {
        $params = input('post.');
       // var_dump($this->userId );exit();
       $validate = new Validate([
           'uploadId'          => 'require|integer',
       ]);

       $validate->message([
           'uploadId.require'          => '上传id不能为空!',
       ]);

       if (!$validate->check($params)) {
           $this->error($validate->getError());
       }

       $fileUploadParts = new FileUploadParts();
       $finishPartList  = $fileUploadParts->getPartList($this->uploadInfo['id']);
       
       $responseData = ['finishPartList'=>$finishPartList];
       $this->success($responseData);

    }

    //建立上传文件夹
    private function touchDir($filePath){
        // var_dump( $this->filePath );exit();
        if(!file_exists($filePath)){
            try {
               mkdir($filePath,0777,true);
            } catch (\Exception $e) {
                $this->error('初始化文件夹失败');
            }
             
        }
    }
    //列举分片上传事件
    // public function listMultipartUploads($value='')
    // {
    //  # code...
    // }

    public function mergeUploadParts($value='')
    {

    }

}
