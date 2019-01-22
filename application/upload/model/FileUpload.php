<?php
// +----------------------------------------------------------------------
// | Author: Forest
// +----------------------------------------------------------------------
namespace app\upload\model;

use think\Model;

class FileUpload extends Model
{
	public function addUpload($data=[])
	{
		return $this->insert($data);
	}

	public function checkUploadId($where=[])
	{
		return $this->where($where)->value('id');
	}

	public function getUploadInfo($where=[])
	{
		$uploadInfo = $this->where($where)->find();

		if (!empty($uploadInfo) ) {
			return $uploadInfo->toArray();
		}

		return $uploadInfo;
	}

	public function changeUploadStatus($uploadId='')
	{
		$where ['id'] = $uploadId;
		$data['status'] = UPLOAD_NO_INFO;
		$data['finish_time'] = time();
		return  $this->where($where)->update($data);
	}

	public function delUploadId($uploadId='')
	{
		$where ['id'] = $uploadId;
		return $this->where($where)->delete();
	}

	public function editVideoInfo($data='')
	{
		$where ['id'] = $data['id'];
		// $data['status'] = UPLOAD_NO_INFO;
		return  $this->where($where)->update($data);
	}

}