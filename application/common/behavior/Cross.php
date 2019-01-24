<?php

namespace app\common\behavior;

use think\Request;
// use think\Config;
// use cmf\controller\RestBaseController;
// use think\controller\Rest;
use think\exception\HttpResponseException;
use think\Response;

class Cross  {

    public function run($dispatch) {
        $hostName = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : "*";
        $headers = [
            'Access-Control-Allow-Origin'      => $hostName,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods'     => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers'     => 'access-control-allow-methods,access-control-allow-origin,content-type, Sign, Auth-Token,xx-device-type',
        ];
        // var_dump(($_SERVER['REQUEST_METHOD']) );exit();
        if($dispatch instanceof Response) {
            $dispatch->header($headers);
        } else if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $type                                   = 'json';
            $response = '';
            $response                               = Response::create($response, $type)->header($headers);
            throw new HttpResponseException($response);
            // json()->code(200)->header($headers)->send();
        }
    }
}