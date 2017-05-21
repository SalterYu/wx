<?php
require("SqlFunction.php");//引用数据库文件
//若老师注册
//echo $_POST['techOpenId'];die;
if(!isset($_POST['openid']))
{
   echo "403 forbidden";
   exit;
}


if(($_POST['type'])=='addlesson'){
    if(($_POST['lessonName'])&&($_POST['lessonClass'])&&($_POST['lessonStart'])&&($_POST['lessonStop'])&&($_POST['techNumber'])&&($_POST['week']))
    {
        $lessonName =($_POST['lessonName']);
        $lessonClass = ($_POST['lessonClass']);
        $lessonStart = ($_POST['lessonStart']);
        $lessonStop = ($_POST['lessonStop']);
        $techNumber = $_POST['techNumber'];
        $week = ($_POST['week']);
        addLesson($lessonName,$lessonClass,$lessonStart,$lessonStop,$techNumber,$week);
    }
    else{
        echo "您输入的信息有误，或者服务器延迟";
    }
}

if(($_POST['type'])=='select'&&(!empty($_POST['lessonName']))&&(!empty($_POST['class']))){
    $sqlconn = new SqlFunction();

    $sqlGetScene_id = "SELECT
            scene_id
        FROM
            signfail
        ";
    $sqlResultGetScene_id = $sqlconn->excudSqlString($sqlGetScene_id,"use signindb");
    $x = 0;
    if($sqlResultGetScene_id->num_rows>0){
        while ($row = $sqlResultGetScene_id -> fetch_assoc()) {
            $scene_id[$x] = $row["scene_id"];
            $x = $x + 1;
        }
    }else 
    {
        $result = array(
        'statue'=>0 ,
        'message'=>"未获取数据"
        );
        echo json_encode($result);die;
    }
    //echo $_POST['time'];die;
    for($y = 0;$y < $x ; $y++){
        if(date("Y-m-d",$scene_id[$y]) == $_POST['time'])
            break;
    }

    if($y>=$x){
        $result = array(
        'statue'=>0 ,
        'message'=>"没有匹配时间的数据"
        );
        echo json_encode($result);die;
    }

    $sqlGetSignFail = "SELECT
            *
        FROM
            signfail
        WHERE
            lesson = '".$_POST['lessonName']."' and
            stuClass = ".$_POST['class']." and
            scene_id = ".$scene_id[$y];
    //echo $_POST['time'];die;
    //echo $sqlGetSignFail;die;
    $sqlResult=$sqlconn->excudSqlString($sqlGetSignFail,"use signindb");

    if($sqlResult->num_rows == 0){ 
         $result = array(
        'statue'=>0 ,
        'message'=>"未获取数据"
        );
        echo json_encode($result);die;
    }
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
    $result = array(
        'statue'=>1,
        'scene_id'=>$scene_id ,
        'stuNum'=>$stuNum,
        'stuName'=>$stuName,
        'lesson'=>$lesson,
        'week'=>$week,
        'stuClass'=>$stuClass,
        'i'=>$i
        );
    echo json_encode($result);die;

}


if(($_POST['type'])=='statistics'&&(!empty($_POST['lessonName']))&&(!empty($_POST['class']))){
    $lesson = $_POST['lessonName'];
    $class = $_POST['class'];
    $sqlconn = new SqlFunction();
    $sqlGetSignFail = 'SELECT
                stuName,stuNum,stuClass,lesson,
                COUNT(*) AS count
              FROM
                signfail
              WHERE 
                lesson = "'.$lesson.'" AND stuClass = '.$class.'
              GROUP BY
                stuNum
              ORDER BY
                COUNT(*) DESC';
    //echo $sqlGetSignFail;die;
    $sqlResult=$sqlconn->excudSqlString($sqlGetSignFail,"use signindb");

    if($sqlResult->num_rows == 0){ 
         $result = array(
        'statue'=>0 ,
        'message'=>"未获取数据"
        );
        echo json_encode($result);die;
    }
    $i = 0;
    while ($row = $sqlResult -> fetch_assoc()) {
        $stuNum[$i] = $row['stuNum'];
        $stuName[$i] = $row['stuName'];
        $stuClass[$i] = $row['stuClass'];
        $stuCount[$i] = $row['count'];
        $i = $i + 1;
    }
    $result = array(
        'statue'=>1,
        'data'=>array(
            'stuNum'=>$stuNum,
            'stuName'=>$stuName,
            'stuClass'=>$stuClass,
            'lesson'=>$lesson,
            'stuCount'=>$stuCount,
            'i'=>$i
            ),
        );
    echo json_encode($result);die;
}
else{
    $result = array(
        'statue'=>0 ,
        'message'=>"您输入的信息有误"
        );
    echo json_encode($result);
}

function addLesson($lessonName='',$lessonClass='',$lessonStart='',$lessonStop='',$techNumber='',$week=''){
    //数据库处理
    $sqlconn = new SqlFunction();
    //先根据信息判定是否已经添加
    $sqlGetEXISTS = "SELECT
                *
            FROM
                lesson
            WHERE
                lessonName = '".$lessonName."' and 
                lessonClass = '".$lessonClass."' and 
                techNumber = '".$techNumber."' and
                week = '".$week."' ";
    $sqlResultGetEXISTS = $sqlconn->excudSqlString($sqlGetEXISTS,"use signindb");
    if($sqlResultGetEXISTS->num_rows != 0){ echo "您已经添加过请忽重复提交";die;}

    $sqlIsertIntoLesson = "INSERT INTO lesson (
        lessonName,
        lessonClass,
        lessonStart,
        lessonStop,
        techNumber,
        week
    ) 
    SELECT
        '".$lessonName."',
        '".$lessonClass."',
        '".$lessonStart."',
        '".$lessonStop."',
        '".$techNumber."',
        '".$week."'
    FROM
        DUAL
    WHERE
        NOT EXISTS (
            SELECT
                *
            FROM
                lesson
            WHERE
                lessonName = '".$lessonName."' and 
                lessonClass = '".$lessonClass."' and 
                techNumber = '".$techNumber."' and
                week = '".$week."' 

    )";

    $sqlResult = $sqlconn->excudSqlString($sqlIsertIntoLesson,"use signindb");
    if($sqlResult)
        $statue = 1;
    else
        $statue = 0;
    echo $statue;
}
//register("1212","1001","学生1",0,'1','1');
?>  