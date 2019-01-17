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

	public function checkUploadId($data=[])
	{
		return $this->where($data)->value('id');
	}

	public function getUploadInfo($data=[])
	{
		return $this->where($data)->find();
	}

}