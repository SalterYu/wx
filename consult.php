<?php

class CodeAccessToken{
    private $appid = '';
    private $appsecret = '';
    public  $accessToken = 'accessToken';
    public  $refreshToken = 'refresh_token';
    //高级接口Api根地址
    private $wechatApiBase = 'https://api.weixin.qq.com/cgi-bin';
    //网页授权根地址
    private $oauth = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
    //获取用户信息地址
    private $userInfo = 'https://api.weixin.qq.com/sns/userinfo';
    public function __construct($appid='',$appsecret='')
    {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
    }

        /**
     * 获取accessToken
     * @code string $code 
     * @return array 请求之后得到的数组
     */
    public function getAccessToken($code='',$state=''){
        $url = "{$this->wechatApiBase}/token";
        $param = array(
            'grant_type' => 'client_credential',
            'appid'=> $this->appid,
            'secret'=> $this->appsecret
        );

        if(!empty($code)){
            $param['grant_type']='authorization_code';
            $param['code'] = $code;
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        }
        //if($code=='051nYIk10E4n6C1lgvi10ZiBk10nYIkA'){
            //echo $url . '?' . http_build_query($param);die;
        //}
        if($state == 'two')
        {
           echo $code ;//die;
        }
        $res= self::http($url,$param);
        $access = json_decode($res,true);        
        //print_r($access);die;
        $code = isset($_GET['code']) ? $_GET['code'] : '';

        if(!empty($access)){
            $this->accessToken = $access['access_token'];
            $this->refreshToken = $access['refresh_token'];
            //$var_dump($this->accessToken);die;
            //var_dump($this->accessToken);die;
            //return $access['access_token'];
        }else{
            throw new Exception("AccessToken获取失败", 1);
            
        }
        return $access;
    }

    public static function http($url,$param = '' ,$data = ' ',$method = 'GET'){
        $opts = array(
            CURLOPT_TIMEOUT=>7200,
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_SSL_VERIFYHOST=>false,
            CURLOPT_SSL_VERIFYPEER=>false,
            );
        //根据get请求参数组织新的url地址
        if($param!=''){
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($param);
            //var_dump($opts[CURLOPT_URL]);
        }
        else{
            $opts[CURLOPT_URL] = $url;
        }

        //进行post请求
        if($method == 'POST'){
            $opts[CURLOPT_POST] = true ;
            $opts[CURLOPT_POSTFIELDS] = $data;
            if(is_string($data)){
                $opts[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/json',
                    'Content-Length:' . strlen($data)
                    );
            }
        }
        //执行curl请求
        $ch=curl_init();
        curl_setopt_array($ch,$opts);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
     }

     /**
      * [jumpOauth description]
      * @param  string $redirect_url [授权后重定向的回调链接地址，请使用urlEncode对链接进行处理]
      * @param  string $scope        [应用授权作用域，可以填写snsapi_base 或者snsapi_userinfo]
      * @param  string $state        [场景表示作区分]
      * @return [type]               [description]
      */
     public function jumpOauth($redirect_uri = '',$scope='snsapi_base',$state='one'){
        $query_arr = array(
            'appid'=> $this->appid,
            'redirect_uri'=>$redirect_uri,
            'response_type'=>'code',
            'scope'=>$scope,
            'state'=>$state
            );
        $url = $this->oauth. http_build_query($query_arr).'#wechat_redirect';
        //echo $url;die;
        header("location:".$url);
     }

      function https_request($url, $data = null)
     {
          $curl = curl_init();
          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
          if (!empty($data)){
           curl_setopt($curl, CURLOPT_POST, 1);
           curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
          }
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
          $output = curl_exec($curl);
          curl_close($curl);
          return $output;
     } 

     /**
      * 拉取用户信息
      * @param  string $access_token [网页授权access_token]
      * @param  string $openid       [用户的openid]
      * @param  string $lang         [语言]
      * @return [array]              [用户信息]
      */
     public function getUserInfo($access_token='',$openid='',$lang='zh_CN'){
        $query_arr = array(
            'access_token'=>$access_token,
            'openid'=>$openid,
            'lang'=>$lang,
        );

        //echo $this->userInfo.'?'.http_build_query($query_arr);die;
        $res = self::http($this->userInfo,$query_arr);
        //print_r($res);die;
        return json_decode($res,true);
     }
      public function OutputTitle(){
          echo $this->accessToken;
      }
      public function OutputContent(){
          echo 'Hello!';
      }
}
date_default_timezone_set("Asia/Shanghai"); 
header("Content-type: text/html; charset=utf-8"); 


//print_r($_SESSION['code_access_token']);die;

session_start();
/*$_SESSION['code_access_token']=array();
session_unset();
session_destroy();die;*/
$url ='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$codeWeb = new CodeAccessToken('wx5bf1351ba19ec858','e78fdc44ce5a4350023d0c20c7150d89');
//$codeWeb->getAccessToken();
$code = isset($_GET['code']) ? $_GET['code'] : '';
$state = isset($_GET['state']) ? $_GET['state'] : '';
/*$_SESSION['code_access_token']=array();
print_r($_SESSION['code_access_token']);die;*/
//判断sesion里面的openid和code是否为空，为空进行一次跳转
if(empty($_SESSION['openid'])&&empty($code)&&empty($state)){
    $codeWeb->jumpOauth($url);
}

//完成第一次跳转通过的code获取的accessToken
$code_access_token = $_SESSION['code_access_token'];
//$count = $_SESSION['count'];
//print_r($code_access_token);
//print_r($count);


$time= time();
if(!empty($code_access_token['time']))
   $time = $time-$code_access_token['time'];
//echo $time;die;
if(!empty($code)&&$state=='one'&&(($time>7200)||empty($code_access_token))){
    //echo $state;die;
    //第二步：通过code换取网页授权access_token
    
    $accessToken = $codeWeb->getAccessToken($code,$state);
    $code_access_token = array(
        'time'=>time(),
        'token'=>$codeWeb->accessToken,
        'openid'=>$accessToken['openid'],
        'refreshToken'=>$codeWeb->refreshToken
    );
    $_SESSION['code_access_token'] = $code_access_token;
    $code_access_token = $_SESSION['code_access_token'];
}
//获取openid
$openid = $code_access_token['openid'];
$token =$code_access_token['token'];
$refreshToken = $code_access_token['refreshToken'];
//print_r($code_access_token);die;
//去数据库获取用户信息，不存在就返回false
//假设用户不存在，我们需要获取授权
if(empty($_SESSION['openid'])&&!empty($code)&&$state=='one'){
    $codeWeb->jumpOauth($url,'snsapi_userinfo','two');
}

$userinfo = $codeWeb->getUserInfo($token,$openid);
    //echo "d0";die;
//第四步：通过code换取网页授权access_token
//echo $state;die;
//$sessionCount = $_SESSION['count'];
if($state=='two'&&(!array_key_exists("openid", $userinfo))){
    {
        $_SESSION['count'] = 0;
        //echo $_SESSION['count'];die;
        $accessToken = $codeWeb->getAccessToken($code,$state);
        $code_access_token = array(
            'time'=>time(),
            'token'=>$codeWeb->accessToken,
            'openid'=>$accessToken['openid'],
            'refreshToken'=>$codeWeb->refreshToken
        );
        $_SESSION['code_access_token'] = $code_access_token;
        $code_access_token = $_SESSION['code_access_token'];
        $openid = $code_access_token['openid'];
        $token =$code_access_token['token'];
        $refreshToken = $code_access_token['refreshToken'];
        $userinfo = $codeWeb->getUserInfo($token,$openid);
    }
}

$province = $userinfo['province'];
$nickname = $userinfo['nickname'];
$sex = $userinfo['sex'];
if($sex == 1)
    $sex = '男';
else
    $sex = '女';

/*$_SESSION['code_access_token']=array();
session_unset();
session_destroy();die;*/
?>


