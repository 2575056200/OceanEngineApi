<?php
namespace didphp\OceanEngineApi;

/**
 * Class QianChuanApi
 * 巨量引擎开放平台千川API
 * @package didphp\OceanEngineApi
 */
class LbbQianChuanApi {

    const DOMAIN = 'https://ad.oceanengine.com';

    const URL = [
        //账号授权地址
        'authorizeUrl' => 'https://qianchuan.jinritemai.com/openapi/qc/audit/oauth.html',
        //获取 access token
        'getAccessToken' => self::DOMAIN . '/open_api/oauth2/access_token/',
        //刷新 access token
        'refreshAccessToken' => self::DOMAIN . '/open_api/oauth2/refresh_token/',
        //获取已授权账户
        'getAdvertiser' => self::DOMAIN . '/open_api/oauth2/advertiser/get/?',
    ];

    private $_nowTime;
    public $config=[];
    private $_configFile;


    public function __construct($config=[]) {
        $this->_nowTime = time();
        $this->config['app_id'] = isset($config['app_id']) ? trim($config['app_id']) : '';
        $this->config['secret'] = isset($config['secret']) ? trim($config['secret']) : '';
        $this->config['auth_code'] = isset($config['auth_code']) ? trim($config['auth_code']) : '';
        $this->config['access_token'] = isset($config['access_token']) ? trim($config['access_token']) : '';
        $this->config['refresh_token'] = isset($config['refresh_token']) ? trim($config['refresh_token']) : '';

        if ($this->config['app_id'] == '') { return false; }

        $basicPath = "qianchuan-api/{$this->config['app_id']}";
        $this->_apiLog = "{$basicPath}/log/" . date('Y-m-d', $this->_nowTime) . "/request_" . date('H', $this->_nowTime) . ".log";

        if ($this->config['auth_code'] == '') {
            $fillConfig = false;
            $this->_configFile = "{$basicPath}/config.json";
            $configFileExist = Storage::disk('public')->exists($this->_configFile);
            if ($configFileExist) {
                $configString = Storage::disk('public')->get($this->_configFile);
                if ($configString != '') {
                    $configData = json_decode($configString, true);
                    if ($configData && $configData['access_token']) {
                        $fillConfig = true;
                    }
                    if ($fillConfig) {
                        if ($this->config['secret'] == '') {
                            $this->config['secret'] = $configData['secret'];
                        }
                        if ($configData['updated'] + $configData['expires_in'] < $this->_nowTime) {

                        }


                        if ($this->config['access_token'] == '') {
                            $this->config['access_token'] = $configData['access_token'];
                        }
                        if ($this->config['refresh_token'] == '') {
                            $this->config['refresh_token'] = $configData['refresh_token'];
                        }
                    }
                }
            }
        }
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
            'auth_code' => $this->config['auth_code'],
        ];
        $result = $this->sendRequest($url, $data, false);
        if ($result && isset($result['code']) && $result['code'] == '0') {
            $data = $result['data'];
            $data['app_id'] = $this->config['app_id'];
            $data['secret'] = $this->config['secret'];
            $data['updated'] = $this->_nowTime;
            Storage::disk('public')->put($this->_configFile, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        return $result;
    }

    public function refreshAccessToken() {
        $url = self::URL['refreshAccessToken'];
        $data = [
            'app_id' => $this->config['app_id'],
            'secret' => $this->config['secret'],
            'grant_type' => 'auth_code',
            'refresh_token' => $this->config['refresh_token'],
        ];
        $result = $this->sendRequest($url, $data, false);
        if ($result && isset($result['code']) && $result['code'] == '0') {
            $data = $result['data'];
            $data['app_id'] = $this->config['app_id'];
            $data['secret'] = $this->config['secret'];
            $data['updated'] = $this->_nowTime;
            Storage::disk('public')->put($this->_configFile, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        return $result;
    }

    public function getAdvertiser() {
        $url = self::URL['getAdvertiser'];
        $data = [
            'app_id' => $this->config['app_id'],
            'secret' => $this->config['secret'],
            'access_token' => $this->config['access_token'],
        ];
        return $this->sendRequest($url, $data);
    }

    private function sendRequest($url, $paramData=[], $isGet=true) {
        if (!$isGet) {
            $jsonString = $this->curl_https($url, $paramData);
            Storage::disk('public')->append($this->_apiLog, "接口：POST");
        } else {
            if ($paramData) {
                foreach ($paramData as $k => $v) {
                    $url .= "{$k}={$v}&";
                }
                $url = substr($url, 0, -1);
            }
            $jsonString = $this->curl_https($url);
            Storage::disk('public')->append($this->_apiLog, "接口：GET");
        }
        Storage::disk('public')->append($this->_apiLog, "URL：{$url}");
        Storage::disk('public')->append($this->_apiLog, "参数：" . json_encode($paramData, JSON_UNESCAPED_UNICODE));
        Storage::disk('public')->append($this->_apiLog, "返回：{$jsonString}\n");
        $jsonData = json_decode($jsonString, true);
        if ($jsonData && isset($jsonData['code'])) {
            return $jsonData;
        } else {
            return ['code' => '-1', 'message' => "本地系统接口请求出错：{$jsonString}"];
        }
    }

    private function curl_https($url,$data=[]){ // 模拟提交数据函数
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
