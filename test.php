<?php
    require("./server/SqlFunction.php");

//获取openid

//print_r($code_access_token);die;
//把openid存入session中
$sqlconn= new SqlFunction();
$openid = 1;
$techNumber = 1000;
$sqlSelectLesson = "select * from lesson where techNumber=".$techNumber ;
$sqlResultSelectLesson = $sqlconn->excudSqlString($sqlSelectLesson,"use signindb");
$l=0;
while($row=$sqlResultSelectLesson->fetch_assoc()){
  $lessonName[$l] = $row['lessonName'];
  $l = $l + 1;
}
?>


<!DOCTYPE html>
<html>
<head>
  <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
<style>
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
</style>
<link rel="stylesheet" href="./bootstrap/css/bootstrap.css" crossorigin="anonymous">
<script src="./js/jquery.js" crossorigin="anonymous"></script>
<script src="./bootstrap/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script>
function sub() { 
var lessonName = $("#select").val();
var clas = $("#class").val();
  $.ajax({  
          url:"./server/lesson_server.php",           //the page containing php script  
          type: "POST",               //request type  
          data:{
            'openid':"<?php echo $openid ?>",
            'type':'select',
            'lessonName':lessonName,
            'class':clas

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
              if(result==1){
                  alert("绑定完成，请去主界面添加您的课程信息"); 
                   //做个重定向,用来录入老师的课程 
              }
              else
                  alert(result)
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
<div class="form-group ">
    <div class="d-sm-none text-center">
            <span type="button" class="btn btn-primary1 btn-large border-0" rel="nofollow" onclick="sub()">Select</span>
    </div>
</form>
<div id="table" style="padding-left: 30px;">
<table  class="table table-striped table-condensed" id="selectTable" >  
    <thead>
        <tr>
          <th><h1>#</h1></th>
          <th><h1>学生学号</h1></th>
          <th><h1>学生姓名</h1></th>
          <th><h1>课程</h1></th>
          <th><h1>星期</h1></th>
          <th><h1>班级</h1></th>
        </tr>
    </thead>

</table>
</div>


<br/>

</body>
</html>
