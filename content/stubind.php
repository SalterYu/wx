<?php
     require("../server/SqlFunction.php");
class CodeAccessToken{

    const MSG_DATABASE = 'use signindb';
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
if(!empty($_SESSION['code_access_token']))
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
//根据openid去数据库获取用户信息，不存在就返回false
$sqlGetInfo = "SELECT * FROM student where stuOpenId='".$openid."'";
$sqlconn = new SqlFunction();
$sqlResultGetInfo = $sqlconn->excudSqlString($sqlGetInfo,"use signindb");
if($sqlResultGetInfo->num_rows != 0){ echo "<h1 style='color:green'>您已经完成注册</h1>";die;}
?>



<!DOCTYPE html>
<html>
<head>
<style>
    .text-center {
    text-align: center !important;
        padding-top: 50px;
}
.border-0 {
    border: 0 !important;
}
.btn-large {
    padding: 0.75em 1.25em;
    font-size: inherit;
    border-radius: 6px;
}
.btn-primary1 {
    color: #fff;
    background-color: #28a745;
    background-image: -webkit-linear-gradient(270deg, #34d058 0%, #28a745 90%);
    background-image: linear-gradient(-180deg, #34d058 0%, #28a745 90%);
}

.btn {
    position: relative;
    display: inline-block;
    padding: 6px 12px;
    font-size: 14px;
    font-weight: 600;
    line-height: 20px;
    white-space: nowrap;
    vertical-align: middle;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-repeat: repeat-x;
    background-position: -1px -1px;
    background-size: 110% 110%;
    border: 1px solid rgba(27,31,35,0.2);
    border-radius: 0.25em;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
</style>
<meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->

<link rel="stylesheet" href="../bootstrap/css/bootstrap.css" crossorigin="anonymous">
<script src="../js/jquery.js" crossorigin="anonymous"></script>
<script src="../bootstrap/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script type="text/javascript">
  function sub() { 
      var name = $("#inputName").val();
      var inputNumber = $("#inputNumber").val();
      var reputNumber = $("#reputNumber").val();
      var stuPro = $("#stuPro").val();
      var stuClass = $("#stuClass").val();
      if(name ==''||name==null){
        alert("你没名字么？");
        return;
       }
      if(inputNumber ==''||inputNumber==null)
      {
        alert("我靠！你工号也不输？");
        return;
      }
      if(inputNumber != reputNumber){
        alert("你不知道两次工号不一样么？")
        return
      }
      if(stuPro==''||stuPro==null){
        alert("大爷的！专业也不输？")
        return
      }
      if(stuClass==''||stuClass==null){
        alert("你没班级的？")
        return
      }
      $.ajax({  
          url:"../server/register_server.php",           //the page containing php script  
          type: "POST",               //request type  
          data:{
            'identity':0,
            'stuOpenId':<?php echo $openid ?>,
            'stuNum':reputNumber,
            'stuName': name,
            'stuPro' : stuPro,
            'stuClass' :stuClass

        },  
          success:function(result){  
              if(result==1)
                  alert("绑定完成");  
              else
                  alert(result)
          }  
      });
  }  
  function blurName(){
    if($("#inputName").val()==''||$("#inputName").val()==null)
      $("#inputName").css("border-color","red");
    else
      $("#inputName").css("border-color","green");
  }
  function blurinputNumber(){
    if($("#inputNumber").val()==''||$("#inputNumber").val()==null)
      $("#inputNumber").css("border-color","red");
    else
       $("#inputNumber").css("border-color","green");
  }

  function checkinputNumber(){
     if($("#reputNumber").val()==''||$("#reputNumber").val()==null){
       $("#reputNumber").css("border-color","red");
       return;
     }
     if($("#inputNumber").val()!=$("#reputNumber").val())
        $("#reputNumber").css("border-color","orange");
     else
        $("#reputNumber").css("border-color","green");

  }
  function blurstuPro(){
    if($("#stuPro").val()==''||$("#stuPro").val()==null)
      $("#stuPro").css("border-color","red");
    else
       $("#stuPro").css("border-color","green");
  }
    function blurstuClass(){
    if($("#stuClass").val()==''||$("#stuClass").val()==null)
      $("#stuClass").css("border-color","red");
    else
       $("#stuClass").css("border-color","green");
  }
</script>
</head>
      
<body class="bg-success">
<form class="form-horizontal" style="padding-top: 20px;">
  <div class="form-group">
    <div class="col-sm-12">
      <input type="text" class="form-control" id="inputName" placeholder="请输入您的真实姓名" onblur="blurName()">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-12">
      <input type="text" class="form-control" id="inputNumber" placeholder="输入学号" onblur="blurinputNumber()">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-12">
      <input type="text" class="form-control" id="reputNumber" placeholder="请重复输入学号" onblur="checkinputNumber()">
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-12">
      <input type="text" class="form-control" id="stuPro" placeholder="请输入专业" onblur="blurstuPro()">
    </div>
  </div>

 <div class="form-group">
    <div class="col-sm-12">
      <input type="text" class="form-control" id="stuClass" placeholder="请输入班级" onblur="blurstuClass()">
    </div>
  </div>
  <div class="d-sm-none text-center">
            <span type="button" class="btn btn-primary1 btn-large border-0" rel="nofollow" onclick="sub()">Bind for System</span>
    </div>

</form>
</body>
</html>
