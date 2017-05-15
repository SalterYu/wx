<?php
date_default_timezone_set("Asia/Shanghai"); 
header("Content-type: text/html; charset=utf-8"); 
$techOpenId = 4;
$openid = $techOpenId;
session_start();
if($techOpenId){
  $_SESSION['openid'] = $techOpenId;
}

function test(){
    $techOpenId = 4;
    echo $techOpenId;
}

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
<link rel="stylesheet" href="./bootstrap/css/bootstrap.css" crossorigin="anonymous">
<script src="./js/jquery.js" crossorigin="anonymous"></script>
<script src="./bootstrap/js/bootstrap.min.js" crossorigin="anonymous"></script>
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
          url:"./server/server.php",           //the page containing php script  
          type: "POST",               //request type  
          data:{
            'identity':0,
            'stuOpenId':<?php echo $techOpenId; ?>,
            'stuNum':reputNumber,
            'stuName': name,
            'stuPro' : stuPro,
            'stuClass' :stuClass

        },  
          success:function(result){  
              if(result==1){
                  alert("绑定完成");  
                  //做个重定向,用来录入老师的课程
              }
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
  function subtest(){
    window.location.href ="test.php";
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
      <input type="text" class="form-control" id="reputNumber" placeholder="请重复输入教工号" onblur="checkinputNumber()">
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
<div class="d-sm-none text-center">
            <span type="button" class="btn btn-primary1 btn-large border-0" rel="nofollow" onclick="subtest()">test</span>
    </div>

</form>
</body>
</html>
