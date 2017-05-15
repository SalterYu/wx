<?php

//设置编码格式
header("Content-type:text/html;charset=utf-8");
//对连接进行判断
class SqlFunction{
        //定义数据库主机地址
    public $host="";
     
    //定义mysql数据库登录用户名
    public $user="";
     
    //定义mysql数据库登录密码
    public $pwd="";
     
    //数据库指令：使用数据库
    public $sqlUseDatabase = "";

    //数据库指令：查询表内容
    public $sqlString = "";

    public function __construct($host="",$user="",$pwd=""){
        $this->host=$host;
        $this->user=$user;
        $this->pwd=$pwd;
    }

    /**
     * 连接数据库
     * @param  [string] $host [主机名]
     * @param  [string] $user [数据库用户名]
     * @param  [string] $pwd  [密码]
     * @return [object]   一个数据库数据数组
     */
    public function sqlConn($host,$user,$pwd){
        $conn = mysqli_connect($host,$user,$pwd);
        if($conn){
            //echo "数据库连接成功！";
            return $conn;
        }
            else{
                //echo "数据库连接失败！";
            }
    }

    /**
     * 使用数据库
     * @param  string $host           [主机名]
     * @param  string $user           [用户名]
     * @param  string $pwd            [密码]
     * @param  string $sqlUseDatabase [使用的数据库指令]
     * @return object   $conn       [result]
     */
    public function sqlUseDatabase($host="localhost",$user="root",$pwd="",$sqlUseDatabase="use mytest"){
        $conn = $this->sqlConn($host,$user,$pwd);
        $connDatabase=$conn->query($sqlUseDatabase);//连接数据库
        if($connDatabase===TRUE){
            //echo "数据库选择成功! ";
            return $conn;
        }
        else{
            echo "Error using database: ";
        }
    }


    /**
     * [要执行的数据库指令]
     * @param  string $sqlString      [数据库要执行的指令]
     * @param  string $host           [主机名]
     * @param  string $user           [主机用户名]
     * @param  string $pwd            [主机密码]
     * @param  string $sqlUseDatabase [数据库选择指令]
     * @return [object]   $result     [数据库指令执行结果数组]
     */
    public function excudSqlString($sqlString="",$sqlUseDatabase="use mytest",$host="localhost",$user="root",$pwd=""){
        //var_dump($sqlString);
        try {
            $conn= $this->sqlUseDatabase($host,$user,$pwd,$sqlUseDatabase);
            
            if($conn){
                $result = $conn->query($sqlString);//执行sqlstring
                return $result;
            }
            else 
            {   

                return 'Error: ' . mysql_error();
            }
        }
        catch (Exception $e) {
            var_dump($e);
            return 'Error: ' . mysql_error();
        }
    }

}
/*date_default_timezone_set("Asia/Shanghai"); 
 $signStart = date("Y-m-d h:i:sa",time());
 var_dump(time());
var_dump($signStart);
echo date("Y-m-d H:i:s",time());*/
/*$sqlString= "insert into test(name,openId) VALUES('asd','asd2')";

//若自定义数据库信息则给下面变量赋值否则默认
$host="localhost";
$user="root";
$pwd="";
$sqlUseDatabase="use mytest";
$sqlResult="";
//$sqlString="select * from test";
$sqlconn = new SqlFunction();
$sqlResult=$sqlconn ->excudSqlString($sqlString,$host,$user,$pwd,$sqlUseDatabase);
var_dump($sqlResult);
//var_dump($sqlResult->num_rows);
 /*if($sqlResult->num_rows>0){
            echo "数据库指令操作成功! ";
            echo "OK";
            var_dump($sqlResult);
            $echostr="";
            while ($row = $sqlResult -> fetch_assoc()) {
                  $echostr = $echostr."学号: ". $row["Id"]. " - Name: ". $row["name"];
           }
           echo $echostr;
    }*/
?>