<?php
//设置编码格式
header("Content-type:text/html;charset=utf-8");
//对连接进行判断
/*if(!$conn){
    die("数据库连接失败！".mysqli_errno());
}else{
    echo "数据库连接成功！";W
    
    if($conn->query($sqlUseDatabase)===TRUE){
        echo "数据库使用成功! ";
        $result = $conn->query($sqlSelect);
        if($result->num_rows>0){
            echo "数据库指令操作成功! ";
            while ($row = $result -> fetch_assoc()) {
                  echo "<br> id: ". $row["Id"]. " - Name: ". $row["name"];
            }
            echo "";
        }
    }
    else{
         echo "Error using database: " . $conn->error;
    }
}*/
class sqlConnection{
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
                echo "数据库连接失败！";
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
            //echo "数据库使用成功! ";
            return $conn;
        }
        else{
            echo "Error using database: ";
        }
    }

    /**
     * [ 要执行的数据库指令]
     * @param  string $sqlString      [数据库要执行的指令]
     * @param  string $host           [主机名]
     * @param  string $user           [主机用户名]
     * @param  string $pwd            [主机密码]
     * @param  string $sqlUseDatabase [数据库选择指令]
     * @return [object]   $result     [数据库指令执行结果数组]
     */
    public function excudSqlString($sqlString="",$host="localhost",$user="root",$pwd="",$sqlUseDatabase="use mytest"){
        $conn= $this->sqlUseDatabase($host,$user,$pwd,$sqlUseDatabase);
        if($conn){
            $result = $conn->query($sqlString);//执行sqlstring
            return $result;
        }
    }
}

//若自定义数据库信息则给下面变量赋值否则默认
/*$host="";
$user="";
$pwd="";*/
$sqlResult="";
$sqlString="select * from test";
$sqlconn = new sqlConnection();
$sqlResult=$sqlconn ->excudSqlString($sqlString);
if($sqlResult->num_rows>0){
            //echo "数据库指令操作成功! ";
            while ($row = $sqlResult -> fetch_assoc()) {
                  echo "id: ". $row["Id"]. " - Name: ". $row["name"];
            }
        }
?>