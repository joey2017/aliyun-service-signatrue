<?php

namespace Aliyun\Signatrue;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class AliyunService {
    /**
     * @var mixed
     */
    protected $accessKeyId;

    /**
     * @var mixed
     */
    protected $accessKeySecret;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    public $actions = ['DescribeVerifyToken', 'DescribeVerifyResult'];


    /**
     * AliyunService constructor.
     */
    public function __construct()
    {
        date_default_timezone_set("GMT");
        $this->accessKeyId     = config('aliyun.key');
        $this->accessKeySecret = config('aliyun.secret');
        $this->setConfig([
            'Format'          => 'JSON',
            'AccessKeyId'     => $this->accessKeyId,
            'SignatureMethod' => 'HMAC-SHA1',
            'Timestamp'       => date($this->getDateTimeFormat()),
            'SignatureNonce'  => $this->signatureNonce(),
        ]);
    }

    /**
     * UserVerifyService constructor.
     * @param $action
     * @param $biz_id
     * @param string $biz_type
     */
    public function init($action, $biz_id, $biz_type = 'verifyuser')
    {
        $this->setConfig([
            'Version'          => '2019-03-07',
            'SignatureVersion' => '1.0',
            'Action'           => $action,
            'BizId'            => $biz_id,
            'BizType'          => $biz_type,
        ]);
        $this->config['Signature'] = $this->computeSignature($this->getConfig(), 'GET', $this->accessKeySecret);

        return $this;
    }

    /**
     * @param array $key
     * @return $this
     */
    public function setConfig(array $key)
    {
        if (is_array($key)) {
            $this->config = array_merge($this->config, $key);
        }

        return $this;
    }

    /**
     * @param null $key
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
        return $this->config;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getDateTimeFormat()
    {
        return 'Y-m-d\TH:i:s\Z';
    }

    /**
     * @return string
     */
    public function signatureNonce()
    {
        return time() . rand(100000000, 200000000);
    }

    /**
     * @param $str
     * @return string|string[]|null
     */
    public function percentEncode($str)
    {
        // 使用urlencode编码后，将"+","*","%7E"做替换即满足ECS API规定的编码规范
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);

        return $res;
    }

    /**
     * @param $parameters
     * @param $method
     * @param $accessKeySecret
     * @return string
     */
    public function computeSignature($parameters, $method, $accessKeySecret)
    {
        // 将参数Key按字典顺序排序
        ksort($parameters);
        // 生成规范化请求字符串
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        // 生成用于计算签名的字符串 stringToSign
        $stringToSign = strtoupper($method) . '&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        // 计算签名，注意accessKeySecret后面要加上字符'&'
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));

        return $signature;
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRequest()
    {
        try {
            $client   = new Client();
            $fullurl  = $this->getUrl() . http_build_query($this->getConfig());
            $request  = new Request('POST', $fullurl);
            $response = $client->send($request);
        } catch (\Exception $e) {
            Log::error('发起实人认证请求失败：' . $e->getMessage(), ['info' => $e->getTraceAsString()]);
        }

        return json_decode($response->getBody(), true);
    }
}
