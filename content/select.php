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
//把openid存入session中
$_SESSION['openid']=$openid;
/*if(!empty($_SESSION['openid'])){
header("location:test.php");die;
}*/
//根据openid去数据库获取用户信息，不存在就返回false

$sqlconn= new SqlFunction();
$sqlGetTeacherInfo = "SELECT * from teacher where techOpenId='".$openid."'";
$sqlResultGetTeacherInfo = $sqlconn->excudSqlString($sqlGetTeacherInfo,CodeAccessToken::MSG_DATABASE);
if($sqlResultGetTeacherInfo->num_rows == 0){ echo "<h1 style='color: #a94442';>用户信息错误，请确保您的身份</h1>" ;die;}
while ($row = $sqlResultGetTeacherInfo -> fetch_assoc()) {
    $techNumber = $row["techNumber"];
    $techName = $row["techName"];
}
//获取查阅信息

$sqlGetSignFail = 'SELECT * from signfail';


$sqlResult=$sqlconn->excudSqlString($sqlGetSignFail,CodeAccessToken::MSG_DATABASE);

if($sqlResult->num_rows == 0){ echo "<h1>未获取到数据</h1>" ;}
$i = 0;
while ($row = $sqlResult -> fetch_assoc()) {
    $scene_id[$i] = $row["scene_id"];
    $stuNum[$i] = $row['stuNum'];
    $stuName[$i] = $row['stuName'];
    $lesson[$i] = $row['lesson'];
    $week[$i] = $row['week'];
    $stuClass[$i] = $row['stuClass'];
    $i = $i + 1;
}

$sqlTopFail = 'SELECT
                *,
                COUNT(*) AS count
              FROM
                signfail
              GROUP BY
                stuNum
              ORDER BY
                COUNT(*) DESC limit 0,3';
$sqlResultTopFail = $sqlconn->excudSqlString($sqlTopFail,CodeAccessToken::MSG_DATABASE);
$j = 0;
while ($row = $sqlResultTopFail -> fetch_assoc()) {
    $stuNumT[$j] = $row['stuNum'];
    $stuNameT[$j] = $row['stuName'];
    $stuClassT[$j] = $row['stuClass'];
    $countT[$j] = $row['count'];
    $j = $j + 1;
}

$sqlSelectLesson = "select * from lesson where techNumber=".$techNumber ;
$sqlResultSelectLesson = $sqlconn->excudSqlString($sqlSelectLesson,CodeAccessToken::MSG_DATABASE);
$l=0;
while($row=$sqlResultSelectLesson->fetch_assoc()){
  $lessonName[$l] = $row['lessonName'];
  $l = $l + 1;
}
/*$_SESSION['code_access_token']=array();
session_unset();
session_destroy();die;*/
?>


<!DOCTYPE html>
<html>
<head>
  <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->

</style>
<link rel="stylesheet" href="../css/site.css" crossorigin="anonymous">
<link rel="stylesheet" href="../bootstrap/css/bootstrap.css" crossorigin="anonymous">
<script src="../js/jquery.js" crossorigin="anonymous"></script>
<script src="../js/sortTable.js" crossorigin="anonymous"></script>
<script src="../bootstrap/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script>
function sub() { 
var lessonName = $("#select").val();
var clas = $("#class").val();
var time = $('#time').val();
  $.ajax({  
          url:"../server/lesson_server.php",           //the page containing php script  
          type: "POST",               //request type  
          data:{
            'openid':"<?php echo $openid ?>",
            'type':'select',
            'lessonName':lessonName,
            'class':clas,
            'time':time

        },  
          success:function(result){  
              var obj = JSON.parse(result)
              if(obj.statue==1){
                console.log(obj);
                createTable(obj);
              }
              if(obj.statue==0){
                alert (obj.message)
                return;
              }
              return;

          }  
      });
}
function deleteTable(){
  var table = document.getElementById('selectTable')
  var tbody = document.getElementsByTagName("tbody")
  if(tbody.length == 0) return
  table.removeChild(tbody[0])
}



function createTable(obj){ 
      deleteTable()
      var parNode = document.getElementById("selectTable"); //定位到table上
      var tbody = document.createElement("tbody"); //新建一个tbody类型的Element节
      var tr = new Array();
      var j=0
      for(var i=0;i<=obj.i-1;i++){
          tr[i] = document.createElement("tr"); //新建一个tr类型的Element节点
              td1 = document.createElement("td"); //新建一个td类型的Element节点
              td2 =  document.createElement("td");
              td3 =  document.createElement("td");
              td4 =  document.createElement("td");
              td5 =  document.createElement("td");
              td6 =  document.createElement("td");

              h1 = document.createElement("h1")
              h1.innerHTML = j + 1 
              td1.appendChild(h1)
              tr[i].appendChild(td1); 

              td2.innerHTML = '<h1>' + obj['stuNum'][j] + '</h1>'
              tr[i].appendChild(td2); 

              td3.innerHTML = '<h1>' + obj['stuName'][j] + '</h1>'
              tr[i].appendChild(td3); 

              td4.innerHTML = '<h1>' + obj['lesson'][j] + '</h1>' 
              tr[i].appendChild(td4); 

              td5.innerHTML = '<h1>' + obj['week'][j] + '</h1>'
              tr[i].appendChild(td5); 

              td6.innerHTML = '<h1>' + obj['stuClass'][j] + '</h1>'
              tr[i].appendChild(td6); 

              j = j +1

           tbody.appendChild(tr[i])
      }
      parNode.appendChild(tbody);
 } 
</script>
</head>
<body class="bg-success">
<form class="form-horizontal" style="padding-top: 50px">
<div class="form-group">
    <label  class="col-sm-3 control-label" style="font-size: 36px;color: grey">请选择课程:</label>
    <div class="col-sm-9">
     <select id="select" class="form-control" style="font-size: 30px;min-height: 50px;">
      <?php for ($lesson=0; $lesson < $l; $lesson++) { 
        echo "<option>".$lessonName[$lesson]."</option>";
      }
      ?>
    </select>
    </div>
</div>
<div>
  <?php 
  $c = "<script>alert(obj);</script>";
  ?>
</div>
<br />
<div class="form-group ">
     <label  class="col-sm-3 control-label" style="font-size: 36px;color: grey;text-align: center;">班级:</label>
     <div class="col-sm-9">
     <input id="class" type="text" class="form-control" style="font-size: 30px;min-height: 50px;" placeholder="请输入班级">
    </div>
</div>
<br />
<div class="form-group ">
     <label  class="col-sm-3 control-label" style="font-size: 36px;color: grey;text-align: center;">大致时间:</label>
     <div class="col-sm-9">
     <input id="time" type="date" class="form-control" style="font-size: 30px;min-height: 50px;" placeholder="请输入时间">
    </div>
</div>
<div class="form-group ">
    <div class="d-sm-none text-center">
            <span type="button" class="btn btn-primary1 btn-large border-0" rel="nofollow" onclick="sub()">Select</span>
    </div>
    </div>
</form>
<div id="table" style="padding-left: 30px;">
<table  class="table table-striped table-condensed" id="selectTable" >  
    <thead>
        <tr>
          <th onclick = "$.sortTable.sort('selectTable',0)" ><h1>#</h1></th>
          <th onclick = "$.sortTable.sort('selectTable',1)" ><h1>学生学号</h1></th>
          <th onclick = "$.sortTable.sort('selectTable',2)"><h1>学生姓名</h1></th>
          <th onclick = "$.sortTable.sort('selectTable',3)"><h1>课程</h1></th>
          <th onclick = "$.sortTable.sort('selectTable',4)"><h1>星期</h1></th>
          <th onclick = "$.sortTable.sort('selectTable',5)"><h1>班级</h1></th>
        </tr>
    </thead>
</table>
</div>
</body>
</html>
