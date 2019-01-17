<?php
// +----------------------------------------------------------------------
// | Author: Forest
// +----------------------------------------------------------------------
namespace app\upload\model;

use think\Model;

class FileUploadParts extends Model
{
	public function addUploadpart($data=[])
	{
		return $this->insert($data);
	}

	public function editUploadpart($data=[])
	{
		$where ['upload_id'] = $data['upload_id'];
		$where ['part_number'] = $data['part_number'];
		return  $this->where($where)->update($data);
	}

	public function checkUploadpart($data=[])
	{
		$where ['upload_id'] = $data['upload_id'];
		$where ['part_number'] = $data['part_number'];
		return  $this->where($where)->value('id');
	
	}

	public function getPartList($uploadId='')
	{
		$fields = 'part_number as partNumber, part_etag as ETag';
		$res = $this->where(['upload_id'=>$uploadId])->column($fields);

		return $res;
	}

	public function delFileParts($uploadId='')
	{
		$where ['upload_id'] = $uploadId;
		return $this->where($where)->delete();
	}
}