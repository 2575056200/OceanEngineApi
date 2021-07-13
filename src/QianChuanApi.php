<?php
namespace Didphp\OceanEngineApi;

/**
 * Class QianChuanApi
 * 巨量引擎开放平台千川API
 * @package didphp\OceanEngineApi
 */
class QianChuanApi {

    const DOMAIN = 'https://ad.oceanengine.com';

    const URL = [
        //账号授权地址
        'authorizeUrl' => 'https://qianchuan.jinritemai.com/openapi/qc/audit/oauth.html',
        //获取 access token
        'getAccessToken' => self::DOMAIN . '/open_api/oauth2/access_token/',
    ];

    private $_nowTime;

    public $config=[];

//
//    private $_timestamp;
//    private $_v = 2;
//    private $_accessToken;
//    private $_refreshToken;
//    private $_accessTokenFile;
//    private $_cachePath;
//    private $_apiLog;
//    private $_config;

    public function __construct($config=[]) {
        $this->config['app_id'] = isset($config['app_id']) ? trim($config['app_id']) : '';
        $this->config['secret'] = isset($config['secret']) ? trim($config['secret']) : '';
        return $this;
    }

    public function generateAuthorizeUrl($data=[]) {
        $url = self::URL['authorizeUrl'] . "?app_id={$this->config['app_id']}";
        foreach ($data as $k => $v) {
            $url .= "&{$k}={$v}";
        }
        return $url;
    }

    public function getAccessToken() {
        $url = self::URL['getAccessToken'];
        $data = [
            'app_id' => $this->config['app_id'],
            'secret' => $this->config['secret'],
            'grant_type' => 'auth_code',
            'auth_code' => uniqid('auth-code-'),
        ];
        $result = $this->sendRequest($url, $data);
        var_dump($result);
    }
    


    private function sendRequest($url, $paramData=[]) {
//        if ($data && count($data) > 0) {
//            ksort($data);
//            $paramsJson = json_encode($data, JSON_UNESCAPED_UNICODE);
//            $paramsJson = str_replace('&', '\u0026', $paramsJson);
//            $paramsJson = str_replace('<', '\u003c', $paramsJson);
//            $paramsJson = str_replace('>', '\u003e', $paramsJson);
//            $paramsJson = str_replace("\/", '/', $paramsJson);
//            //$paramsJson = str_replace("\\\\\\", '', $paramsJson);
//        } else {
//            $paramsJson = '{}';
//        }

//        $sign = "app_key{$this->_appKey}method{$this->_method}param_json{$paramsJson}timestamp{$this->_timestamp}v{$this->_v}";
//        $postData = [
//            'method' => $this->_method,
//            'app_key' => $this->_appKey,
//            'access_token' => $this->_accessToken ? $this->_accessToken : '',
//            'param_json' => $paramsJson,
//            'timestamp' =>  $this->_timestamp,
//            'v' => $this->_v,
//            'sign' => md5("{$this->_appSecret}{$sign}{$this->_appSecret}"),
//        ];
//        $jsonString = $this->curl_post_https($url, $paramData);
        return $this->curl_post_https($url, $paramData);
//        Storage::disk('public')->append($this->_apiLog, "接口：{$this->_method} 被调用");
//        Storage::disk('public')->append($this->_apiLog, "URL：{$url}");
//        Storage::disk('public')->append($this->_apiLog, "参数：" . json_encode($postData, JSON_UNESCAPED_UNICODE));
//        Storage::disk('public')->append($this->_apiLog, "返回：{$jsonString}");
//        $jsonData = json_decode($jsonString, true);
//        if ($jsonData && isset($jsonData['err_no']) && $jsonData['err_no'] == '0') {
//            return $jsonData;
//        } else {
//            return ['err_no' => '-1', 'message' => "本地系统接口请求出错：{$jsonString}"];
//        }
    }

    private function curl_post_https($url,$data=[]){ // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        if ($data && count($data) > 0) {
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
    }
}
