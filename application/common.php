<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 递归数组，下划线转驼峰函数；eg: go_to_school => goToSchool
 * @param $data array 需要格式化的数据
 * @param $valFormat bool 是否需要把value也格式化
 * @return mixed
 */
function output_format($data, $valFormat = false)
{
    if (is_object($data)) {
        $data = $data->toArray();
    }
    if (!is_array($data) || empty($data)) {
        return [];
    }
    foreach ($data as $key => $val) {
        //下划线转驼峰
        $newKey = lcfirst(str_replace(' ', '', ucwords(str_replace(['_'], ' ', $key))));

        //对象转数组
        if (is_object($val)) {
            $val = $val->toArray();
        }

        //递归val
        if (is_array($val)) {
            $formatData[$newKey] = output_format($val, $valFormat);
        } else {
            //接口不返回null
            if ($val === null) {
                $val = '';
            }
            $formatData[$newKey] = $val;
        }
    }
    return $formatData;
}

//用户中心
function curl_user($url, $data=[],$header='')
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header );
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // curl_setopt($curl, CURLOPT_USERPWD, $hashPincode);
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $sContent = curl_exec($curl);
    $aStatus = curl_getinfo($curl);
    curl_close($curl);
    // dump(json_decode($sContent));
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}