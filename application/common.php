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
 * 封装返回值
 * @param $errorCode
 * @param $data
 * @return array
 */
function return_result($errorCode, $msg='', $data = [] ) {
    $response = json_encode( [
        'errorcode' => $errorCode,
        // 'message'   => Config::pull('errorcode')[$errorCode],
        'message'   => $msg,
        'data'      => $data
    ]);

    return $response;
}


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