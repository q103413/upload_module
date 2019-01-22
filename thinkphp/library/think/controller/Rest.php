<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\controller;

use think\App;
use think\Request;
use think\Response;
use think\exception\HttpResponseException;

abstract class Rest
{

    protected $method; // 当前请求类型
    protected $type; // 当前资源类型
    // 输出类型
    protected $restMethodList    = 'get|post|put|delete';
    protected $restDefaultMethod = 'get';
    protected $restTypeList      = 'html|xml|json|rss';
    protected $restDefaultType   = 'html';
    protected $userId            = '';
    protected $restOutputType    = [ // REST允许输出的资源类型列表
        'xml'  => 'application/xml',
        'json' => 'application/json',
        'html' => 'text/html',
    ];

    /**
     * 构造函数 取得模板对象实例
     * @access public
     */
    public function __construct()
    {
        // 资源类型检测
        $request = Request::instance();
        $token = $request->header('Auth-Token');
        $this->userId = $this->initUser($token);
        $ext     = $request->ext();
        if ('' == $ext) {
            // 自动检测资源类型
            $this->type = $request->type();
        } elseif (!preg_match('/(' . $this->restTypeList . ')$/i', $ext)) {
            // 资源类型非法 则用默认资源类型访问
            $this->type = $this->restDefaultType;
        } else {
            $this->type = $ext;
        }
        // 请求方式检测
        $method = strtolower($request->method());
        if (!preg_match('/(' . $this->restMethodList . ')$/i', $method)) {
            // 请求方式非法 则用默认请求方法
            $method = $this->restDefaultMethod;
        }
        $this->method = $method;
    }

    private function initUser($token='')
    {
        //校验是否登录
        $header = array('Content-Type: application/json');
        $data = ['token'=>$token];
        $url = 'http://23.224.135.242:8090/user/checktoken';
        $result = curl_user($url, json_encode($data), $header);

        $result = json_decode( $result, 'true');
        if ($result['code'] != 0) {
            $this->error($result['msg']);
        }

        if (empty($result['data']['uid']) ) {
            $this->error('用戶id错误');
        }

         return $result['data']['uid'];

         // return 1;
    }

    /**
     * REST 调用
     * @access public
     * @param string $method 方法名
     * @return mixed
     * @throws \Exception
     */
    public function _empty($method)
    {
        if (method_exists($this, $method . '_' . $this->method . '_' . $this->type)) {
            // RESTFul方法支持
            $fun = $method . '_' . $this->method . '_' . $this->type;
        } elseif ($this->method == $this->restDefaultMethod && method_exists($this, $method . '_' . $this->type)) {
            $fun = $method . '_' . $this->type;
        } elseif ($this->type == $this->restDefaultType && method_exists($this, $method . '_' . $this->method)) {
            $fun = $method . '_' . $this->method;
        }
        if (isset($fun)) {
            return App::invokeMethod([$this, $fun]);
        } else {
            // 抛出异常
            throw new \Exception('error action :' . $method);
        }
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed     $data 要返回的数据
     * @param String    $type 返回类型 JSON XML
     * @param integer   $code HTTP状态码
     * @return Response
     */
    protected function response($data, $type = 'json', $code = 200)
    {
        $response = Response::create($data, $type)->code($code);
        throw new HttpResponseException($response);
    }

    protected  function success($data=[], $errorcode=200, $msg='success')
    {
        $response =  [
            'errorcode' => $errorcode,
            'message'   => $msg,
            'data'      => $data
        ];

        $this->response($response);
    }

    protected  function error($msg='fail',$data=[], $errorcode =500)
    {
        $response =  [
            'errorcode' => $errorcode,
            'message'   => $msg,
            'data'      => $data
        ];

        $this->response($response);
    }

    /**
     * 获取当前登录用户的id
     * @return int
     */
    protected function getUserId()
    {
        if (empty($this->userId)) {
            $this->error('用户未登录');
        }
        return $this->userId;


    }

}
