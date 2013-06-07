<?php
/**
 * Created by JetBrains PhpStorm.
 * User: tglgx
 * Date: 13-3-28
 * Time: 上午8:17
 * To change this template use File | Settings | File Templates.
 */

function json_decode_nice($json, $assoc = true){
    $json = str_replace(array("\n","\r"),"",$json);
    $json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/','$1"$3":',$json);

    $json = preg_replace('/[\x0-\x1f]/', '', $json);

    return json_decode($json,$assoc);
}

class cls_qqapi {
    private $nd ;

    function __construct(){
        $this->nd = new cls_netdo();
    }

    function _DJB($str){
        $hash = 5381;
        for ($i = 0, $len =strlen($str); $i < $len; ++$i)
        {

            $hash += ($hash << 5) + ord($str[$i]);
        }
        return strval($hash & 0x7fffffff);
    }
    function _getGroupACSRFToken(){
        $ck = $this->nd->getCookie('skey');
        if(empty($ck)) return "";
        return $this->_DJB($ck);
    }

    final public function getInfo(){
        return $this->nd->getInfo();
    }

    /**
     * 获取登录验证码
     * @param string $uin QQ号码
     * @return mixed
     */
    final public function getVerifyCodeStream($uin='0'){

        //$body =  $this->nd->Get("https://ssl.captcha.qq.com/getimage?uin=`$uin`&aid=1003903&".mt_rand());
        $url = "http://captcha.qq.com/getimage?uin=$uin&aid=549000912&".mt_rand();
        $body =  $this->nd->Get($url);
        return $body;
    }

    /**
     * 登录
     * @param $uid QQ号码
     * @param $pwd 加密后的密码
     * @param $vcode 验证码
     * @return array {state:true|false}
     */
    final public function login($uid,$pwd,$vcode){
        $body =  $this->nd->Get("http://ptlogin2.qq.com/login?u=$uid&p=$pwd&verifycode=$vcode&aid=549000912&remember_uin=1&webqq_type=10&pttype=1&login2qq=1&u1=http%3A%2F%2Fqun.qq.com%2Fair%2F&h=1&ptredirect=1&ptlang=2052&from_ui=1&dumy=&fp=loginerroralert&action=13-39-1064889&mibao_css=&t=1&g=1&js_type=0&js_ver=10013&login_sig=t6iZlMJXLX2hDiPMVSYnbGHFuZHYssuNPZqzJqyj210fH37Zkv27H2xAyYtwqigx&u1=http%3A%2F%2Fqun.qq.com%2Fair%2F", "https://ui.ptlogin2.qq.com/cgi-bin/login?style=1&pt_safe=1&appid=2001601&s_url=http://qun.qq.com/air/",false,true);

        $body = str_replace('ptuiCB(','[',$body);
        $body = str_replace(');',']',$body);
        $body = str_replace("'",'"',$body);

        $body = json_decode_nice($body);

        if($body[4]!='登录成功！'){
            return array('state'=>false,'message'=>$body[4],'error_code'=>$body[0]);
        }else{
            return array('state'=>true);
        }
    }

    /**
     * 获取当前登录用户群列表
     * @return array|mixed
     */
    final public function getGroupList(){

        $uin = $this->nd->getCookie("uin");

        if(empty($uin)) return array('state'=>false,'message'=>'登录用户cookie为空，请确认是否已登录');
        $uin = substr($uin,1,strlen($uin)-1);

        $url = "http://qun.qzone.qq.com/cgi-bin/get_group_list?uin={$uin}&random=".(mt_rand(0,100000000)/100000000)."&g_tk=" . $this->_getGroupACSRFToken();
        $body =  $this->nd->Get($url);

        $body = str_replace('_Callback(','',$body);
        $body = str_replace(');','',$body);

        $body = json_decode_nice($body);
        if(empty($body)){
            return array('state'=>false,'message'=>'获取数据失败');
        }
        if(empty($body["data"])||empty($body["data"]["group"])){
            $body["state"] = false;
            return $body;

        }
        return array("state"=>true,"data"=>$body["data"]["group"]);
    }

    /**
     * 获取群成员列表
     * @param $gid
     * @return array|mixed
     */
    final public function getGroupMemberList($gid){
        $uin = $this->nd->getCookie("uin");
        if(empty($uin)) return array('state'=>false,'message'=>'登录用户cookie为空，请确认是否已登录');
        $uin = substr($uin,1,strlen($uin)-1);

        $url = "http://qun.qzone.qq.com/cgi-bin/get_group_member?uin=$uin&groupid=$gid&random=".(mt_rand(0,100000000)/100000000)."&g_tk=".$this->_getGroupACSRFToken();
        $body =  $this->nd->Get($url);

        $body = str_replace('_Callback(','',$body);
        $body = str_replace(');','',$body);

        $body = json_decode($body,true);

        if(empty($body)){
            return array('state'=>false,'message'=>'获取数据失败');
        }
        if(empty($body["data"])||empty($body["data"]["item"])){
            $body["state"] = false;
            return $body;

        }
        return array("state"=>true,"data"=>$body["data"]["item"]);

        return $body;
    }

    /**
     * 获取成员头像地址
     * @param $uin
     * @return string
     */
    final public function getMemberFaceSrc($uin){
        $ser = (int)$uin % 4 +1;
        return "http://qlogo{$ser}.store.qq.com/qzone/{$uin}/{$uin}/50";
    }
}