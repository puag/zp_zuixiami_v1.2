<?php
class cls_netdo {
    private $ch;
    private $cookieObject;

    function __construct() {
        $this->ch = curl_init();

        if (isset($_SESSION["__qqapi_cookies_obj__"])) {
            $this->cookieObject = $_SESSION["__qqapi_cookies_obj__"];
        } else {
            $_SESSION["__qqapi_cookies_obj__"] = $this->cookieObject = array();
        }
        /*
        if(isset($_SESSION["__qqapi_cookies_file__"])){
            $cookie_file =$_SESSION["__qqapi_cookies_file__"];
        }else{
            $_SESSION["__qqapi_cookies_file__"] = $cookie_file = tempnam('./temp','cookie');
        }
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookie_file);
        */


        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/4.0; QQDownload 685; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; .NET4.0E)'); //UA
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 40); //超时
        //curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->ch, CURLOPT_ENCODING, 'UTF-8');
    }

    function __destruct() {
        curl_close($this->ch);
    }

    function _updateCookies($server_output) {
        preg_match_all("/Set-Cookie: (.*?)\r\n/i", $server_output, $matches);
        if ($matches[1]) {
            foreach ($matches[1] as $v) {
                preg_match("/ Domain=(.*?); /i", $v, $d);

                $para = explode('; ', $v, 2);

                $key  = explode('=', $para[0], 2);
                $this->cookieObject[$key[0]] = $key[1];
            }
        }
        $_SESSION["__qqapi_cookies_obj__"] = $this->cookieObject;
    }

    function _fillCookies() {

        $obj = $this->cookieObject;
        $str = '';
        foreach ($obj as $key => $val) {
            if (!empty($val)) {
                $str = $str . $key . '=' . $val . '; ';
            }
        }
        //;$str = $str . '_____=0';

        curl_setopt($this->ch, CURLOPT_COOKIE, $str);

        return $str;
    }

    final public function getCookiesStr() {
        return $this->_fillCookies();
    }

    final public function setProxy($proxy = 'http://192.168.0.103:3128') {
        //curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
        //curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);//HTTP代理
        //curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);//Socks5代理
        curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
    }

    final public function setReferer($ref = '') {
        if ($ref != '') {
            curl_setopt($this->ch, CURLOPT_REFERER, $ref); //Referrer
        }
    }

    final public function setCookie($key, $value = '') {
        $this->cookieObject[$key] = $value;
    }

    final public function getCookie($key = '') {
        if (!empty($key)) {
            return $this->cookieObject[$key];
        }

        return $this->cookieObject;

    }

    final public function getInfo() {
        return curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
    }

    final public function Get($url, $ref = '', $nobody = false) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, false); //POST

        if ($ref != '') {
            curl_setopt($this->ch, CURLOPT_REFERER, $ref); //Referrer
        } else {
            curl_setopt($this->ch, CURLOPT_REFERER, $url);
        }

        $this->_fillCookies();

        curl_setopt($this->ch, CURLINFO_HEADER_OUT, 1);
        // 获取头部信息
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        // 返回原生的（Raw）输出
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($this->ch, CURLOPT_NOBODY, $nobody); //不需要内容

        $content = curl_exec($this->ch);
        list($header, $body) = explode("\r\n\r\n", $content);

        $this->_updateCookies($header);

        return $body;
    }

    final public function Post($url, $data = array(), $ref = '', $nobody = false) {
        curl_setopt($this->ch, CURLOPT_URL, $url);

        if ($ref != '') {
            curl_setopt($this->ch, CURLOPT_REFERER, $ref); //Referrer
        } else {
            curl_setopt($this->ch, CURLOPT_REFERER, $url);
        }

        $this->_fillCookies();

        // 获取头部信息
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        // 返回原生的（Raw）输出
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($this->ch, CURLOPT_NOBODY, $nobody); //不需要内容
        curl_setopt($this->ch, CURLOPT_POST, true); //POST
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $content = curl_exec($this->ch);
        list($header, $body) = explode("\r\n\r\n", $content);

        $this->_updateCookies($header);

        return $body;

    }

}