<?php
/**
 * 入口安全控制，签名验证，重放攻击防御
 * @createTime 2018/11/15 11:04
 */
namespace app\common\behavior;

use think\Request;
use think\Config;
// use cmf\controller\RestBaseController;
use think\exception\HttpResponseException;
use think\Response;

class Security {

    /**
     * 验证签名以及重放攻击检测
     */
    public function run($dispatch) {
        // var_dump('expression');exit();
        //排除不执行的方法, 配置文件中配置
        $request = Request::instance();
        $method = $request->method();//获取上传方式
        // $params = $request->param();//获取所有参数，最全
    //     $get = $request->get();获取get上传的内容
        $postParams = $request->post();//获取post上传的内容
        // $token = $request->header('user-token');
        // $request->file('file')获取文件
        //校验是否登录
        // $header = array('Content-Type: application/json');

        // $data = ['token'=>$token];
        // $url = 'http://23.224.135.242:8090/user/checktoken';
        // $result = curl_user($url, json_encode( $data), $header);

        // $result = json_decode( $result, 'true');
        // if ($result['code'] != 0) {
        //     // echo return_result(500, $result['msg'], '');
        // }
        // var_dump($token);exit();
    }
}