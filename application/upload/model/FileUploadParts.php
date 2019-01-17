<?php
// +----------------------------------------------------------------------
// | Author: Forest
// +----------------------------------------------------------------------
namespace app\upload\model;

use think\Model;

class FileUploadParts extends Model
{
	public function addUploadparts($data=[])
	{
		return $this->insert($data);
	}

	public function getPartList($uploadId='')
	{
		$fields = 'part_number as partNumber, part_etag as ETag';
		return $this->field($fields)->where(['upload_id'=>$uploadId])->select();
	}

}