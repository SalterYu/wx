<?php
require("SqlFunction.php");//引用数据库文件
    //若老师注册
    //echo $_POST['techOpenId'];die;
     if(!isset($_SESSION['HTTP_REFERER']))   {   
       echo "非法跳转";
      //header("location: http://www.baidu.com");   
      exit;   
      }   
    if($_POST['identity']==1){
        if(($_POST['techOpenId'])&&($_POST['techNumber'])&&($_POST['techName']))
        {
            $identity =($_POST['identity']);
            $techOpenId = ($_POST['techOpenId']);
            $techNumber = ($_POST['techNumber']);
            $techName = ($_POST['techName']);
            register($techOpenId,$techNumber,$techName,$identity);
        }
    }
    //若学生注册
    if(($_POST['identity'])==0){
        if((($_POST['stuOpenId']))&&(($_POST['stuNum']))&&(($_POST['stuName']))&&(($_POST['stuPro']))&&(($_POST['stuClass'])))
        {
            $identity =($_POST['identity']);
            $stuOpenId = ($_POST['stuOpenId']);
            $stuNum = ($_POST['stuNum']);
            $stuName = ($_POST['stuName']);
            $stuPro = ($_POST['stuPro']);
            $stuClass = ($_POST['stuClass']);
            register($stuOpenId,$stuNum,$stuName,$identity,$stuPro,$stuClass);
        }
    }


    function register($OpenId='',$Number='',$Name='',$identity=0,$stuPro='',$stuClass=''){
        //数据库处理
        $sqlconn = new SqlFunction();
        //若身份是老师
        if($identity==1){
            //先根据真实姓名在alluser表中获取是否身份正确
            $sqlGetIdentity = "SELECT * FROM alluser where identity='".$identity."' and userName= '".$Name."' and userNumber='".$Number."'";
            $sqlResultGetIdentity = $sqlconn->excudSqlString($sqlGetIdentity,"use signindb");
            if($sqlResultGetIdentity->num_rows == 0){ echo "Error: 请确认您的身份信息";die;}
            //防止重复绑定
            $sqlGetInfo = "SELECT * FROM teacher where techNumber='".$Number."' and techName='".$Name."'";
           // echo $sqlGetInfo;die;
            $sqlResultGetInfo = $sqlconn->excudSqlString($sqlGetInfo,'use signindb');
            if($sqlResultGetInfo->num_rows != 0){ echo "Error: 请不要重复绑定";die;}

            $sqlInsertIntoTeacher = "INSERT INTO teacher(techOpenId,techNumber,techName) values('".$OpenId."','".$Number."','".$Name."')";
            $sqlResult = $sqlconn->excudSqlString($sqlInsertIntoTeacher,"use signindb");
            if(!$sqlResult){
                 echo 'Error: 数据异常'; die;
            }
            $statue = 1;
            echo $statue;
        }
        //若身份是学生
        if($identity==0){
            $sqlGetIdentity = "SELECT * FROM alluser where identity='".$identity."' and userName= '".$Name."' and userNumber='".$Number."'";
            $sqlResultGetIdentity = $sqlconn->excudSqlString($sqlGetIdentity,"use signindb");
            //echo $sqlGetIdentity;die;
            if($sqlResultGetIdentity->num_rows == 0){ echo "Error:0, 请确认您的身份信息";die;}

             //防止重复绑定
            $sqlGetInfo = "SELECT * FROM student where stuNum='".$Number."' and stuName='".$Name."'";
           // echo $sqlGetInfo;die;
            $sqlResultGetInfo = $sqlconn->excudSqlString($sqlGetInfo,'use signindb');
            if($sqlResultGetInfo->num_rows != 0){ echo "Error: 请不要重复绑定";die;}

            $sqlInsertIntoStudent = "INSERT INTO student(stuOpenId,stuNum,stuName,stuPro,stuClass) values('".$OpenId."','".$Number."','".$Name."','".$stuPro."','".$stuClass."')";
            $sqlResult = $sqlconn->excudSqlString($sqlInsertIntoStudent,"use signindb");
           // echo $sqlInsertIntoStudent;die;
            if(!$sqlResult){
                 echo 'Error: 数据异常'; die;
            }
            $statue = 1;
            echo $statue;
        }
    }

    //register("1212","1001","学生1",0,'1','1');
?>  