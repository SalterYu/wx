<?php
require("./server/SqlFunction.php");//引用数据库文件
require("./server/createTicket.php");//引用二维码生成文件
class WeiXinCheck{
    const MSG_TYPE_TEXT = 'text';
    const MSG_TYPE_IMAGE = 'image';
    const MSG_TYPE_VOICE ='voice';
    const MSG_TYPE_VIEDO = 'video';
    const MSG_TYPE_MUSIC = 'music';
    const MSG_TYPE_NEWS = 'news';
    const MSG_DATABASE = 'use signindb';
    protected $token = '';
    protected $appID = '';
    protected $appsecret = '';
    public $data=array();
    public function __construct($token,$appID ='',$appsecret = ''){
        $this->token = $token;
        $this->appID = $appID;
        $this->appsecret = $appsecret;
        if(isset($_GET['echostr'])){
            $this->checkToken();
            die;
        }
        else{
            $this->result();
        }
    }

    /**
    *返回获取到的数据数组
    *@return array
    */
    public function response(){
        return $this->data;
    }

    /**
     *验证Url是否有效
     *@return String 验证执行需要输出的字符串
     */
    private function checkToken(){
        $signature =$_GET['signature'];
        $timestamp=$_GET['timestamp'];
        $nonce=$_GET['nonce'];
        $tmpArr=array($this->token,$timestamp,$nonce);
        sort($tmpArr);
        $tmpStr=sha1(implode($tmpArr));
        if($signature==$tmpStr){
            echo $_GET['echostr'];
        }
    }

    /**
    *获取微信服务器发送来的数据
    */
    public function result(){
        $postString = $GLOBALS['HTTP_RAW_POST_DATA'];
        libxml_disable_entity_loader(true);
        $xml = simplexml_load_string($postString,'SimpleXMLElement',LIBXML_NOCDATA);
        //判定微信端发送的数据类型
        /*if($xml->{"Content"}=='1'){
            echo "发送的数据是1";die;
        }*/
        foreach ($xml as $key => $value) {   //强制转换防止乱码
            $this->data[$key] = strval($value);
        }
        return $xml;
    }

    /**
     * 处理回复方法
     * @param  [...] $content 需要回复的数组
     * @param  string $type 消息类型
     */
    public function reply($content,$type = self::MSG_TYPE_TEXT)
    {
        $data = array(
            'ToUserName' => $this->data['FromUserName'],
            'FromUserName' => $this->data['ToUserName'],
            'CreateTime' => time(),
            'MsgType' => $type
            );
        $content = call_user_func(array('self',$type),$content);
        if($type==self::MSG_TYPE_TEXT||$type==self::MSG_TYPE_NEWS){
            $data = array_merge($data,$content);
        }else{
            $data[ucfirst($type)] = $content;
        }
        $xml = new SimpleXMLElement('<xml></xml>');
        self::dataXml($xml,$data);
        //return $data;
        exit($xml->asXML());
    }

    /**
     * 处理xml子节点，创建xml给微信服务器返回回去
     * @param  object $xml xml对象
     * @param  array $data 需要返回给微信服务器的信息数组
     */
    private static function dataXml($xml,$data,$item = 'item'){
        foreach ($data as $key => $value) {
            /*指定默认的数字key*/
            is_numeric($key) && $key = $item;  //这个等于号有毒
            //echo var_dump(is_numeric($key) && $key == $item);
            //判断是否其它类型，如果存在其它类型则回调继续拼接
            if(is_array($value) || is_object($value)){
                $child = $xml -> addChild($key);
                self::dataXml($child,$value,$item);
            }else{
                if(is_numeric($value)){
                    $child = $xml->addChild($key,$value);
                }else{
                    $child = $xml -> addChild($key);
                    $node = dom_import_simplexml($child);
                    $cdata = $node->ownerDocument->createCDATASection($value);
                    $node->appendChild($cdata);
                }
            }
           
        }
    }
    /**
    *组织文本回复数组
    *@param string $content 文本内容
    *@return array 处理好的文本回复数组
    */
    private static function text($content){
        $data['Content'] = $content;
        return $data;
    }

    /**
    *@param string $content 文本回复内容
    *@return string 输出微信服务器的xml
    */
    public function replyText($content){
        $this->reply($content);
    }

     /*组织图片回复数组
    *@param string $MediaId 图片内容
    *@return array 处理好的图片回复数组
    */
    private static function image($MediaId){
        $data['MediaId'] = $MediaId;
        return $data;
    }

    /**
    *@param string $MediaId 图片回复媒体ID
    *@return string 输出微信服务器的xml
    */
    public function replyImage($MediaId){
        $this->reply($MediaId,self::MSG_TYPE_IMAGE);
    }

      /*组织语音回复数组
    *@param string $MediaId 语音媒体ID
    *@return array 处理好的语音回复数组
    */
    private static function voice($MediaId){
        $data['MediaId'] = $MediaId;
        return $data;
    }

    /**
    *@param string $MediaId 语音回复媒体ID
    *@return string 输出微信服务器的xml
    */
    public function replyVoice($MediaId){
        $this->reply($MediaId,self::MSG_TYPE_VOICE);
    }

      /*组织视频回复数组
    *@param string $MediaId 视频媒体ID
    *@return array 处理好的视频回复数组
    */
    private static function video($content){
        $data = array();
        list(
            $data['MediaId'],
            $data['Title'],
            $data['Description']
        ) = $content;
        return $data;
    }

    /**
    *@param string $MediaId 视频回复媒体ID
    *@param  string $Title 视频消息的标题
    *@param  string  $Description 视频消息的描述
    *@return string 输出微信服务器的xml
    */
    public function replyVideo($MediaId,$Title,$Description){
        $this->reply(func_get_args(),self::MSG_TYPE_VIEDO);
    }

    /*组织音乐回复数组
    *@param string $content 音乐回复信息
    *@return array 处理好的音乐回复数组
    */
    private static function music($content){
        $data = array();
        list(
            $data['Title'],
            $data['Description'],
            $data['MusicUrl'],
            $data['HQMusicUrl'],
            $data['ThumbMediaId'],
        ) = $content;
        return $data;
    }

    /**
    *@param string $Title 音乐标题
    *@param string $Description 音乐描述
    *@param string $MusicURL  音乐链接
    *@param string $HQMusicUrl 高质量音乐链接，WIFI环境优先使用该链接播放音乐
    *@param string $ThumbMediaId 缩略图的媒体id，通过素材管理中的接口上传多媒体文件，得到的id
    *@return String 输出微信服务器的xml
    */
    public function replyMusic($Title,$Description,$MusicUrl,$HQMusicUrl,$ThumbMediaId){
        $this->reply(func_get_args(),self::MSG_TYPE_MUSIC);
    }

    /**
     * @param  array $article 图文回复消息
     * @return array 组织好的图文回复数组
     */
    private static function news($article){
        $articles = array();
        foreach ($article as $key => $value) {
            list(
                $articles[$key]['Title'],
                $articles[$key]['Description'],
                $articles[$key]['PicUrl'],
                $articles[$key]['Url'],
            ) = $value;
            if($key>9){
                break;
            }
        }
        $data['ArticleCount'] = count($articles);
        $data['Articles'] = $articles;
        return $data;
    }

    /**
     * @param   array $news1 第一条图文回复消息
     *                       [图文消息标题，图文消息描述，图片链接，点击图文消息跳转链接]
     * @param   array $news1 第二条图文回复消息
     *                       [图文消息标题，图文消息描述，图片链接，点击图文消息跳转链接]
     * @return string  输出微信服务器的xml
     */
    public function replyNews($news1,$news2){
        $this->reply(func_get_args(),self::MSG_TYPE_NEWS);
    }
}
date_default_timezone_set("Asia/Shanghai"); 
$weixin = new WeiXinCheck("weixin");
/**/
//使用testTicket.php文件的方法并初始化accessToken,调用getAccessToken()方法会把那个文件的accessToken变量赋值为微信服务器返回的accessToken
$network = new NetWork_Requests('wx5bf1351ba19ec858','e78fdc44ce5a4350023d0c20c7150d89');
$network -> getAccessToken();

/**/

//print_r($weixin->replyText("dd")); //文字回复
//print_r($weixin->replyImage("udrDpCeKGR61qOiAN8L20YFOXDpEQeRb56W8cA4xCYwGiI5wX5xbxncSgQ6OZhBA"));//图片回复
//print_r($weixin->replyVoice("MMhMsG1O52OM6etF9lT0SFuCsC8896PnUkF1B7Rg7ic2WoWMOGEZwd_fd22sgGNV")); //音频回复
//print_r($weixin->replyVideo("1EsShoFL-ECxRQJNMqA4AQsVao2LjZ7XPBwZj1miqdediBeftzjbmBcIv0A02sQS","测试视频","测试")); //视频回复有错误
/*$weixin->replyMusic("音乐标题","音乐描述","
http://sh-ctfs.ftn.qq.com/ftn_handler/eb6047a7da16feda55b6ccece12014733b1fd7c20292f9fff1f471fe573a8c4f9744183e1262c000fb5e38c0644e5a11427e3ddd9f034f50da497279bda238e7/?fname=%E5%B0%8F%E7%BC%98%20-%20%E8%A2%AB%E9%A9%AF%E6%9C%8D%E7%9A%84%E8%B1%A1.mp3","
http://sh-ctfs.ftn.qq.com/ftn_handler/eb6047a7da16feda55b6ccece12014733b1fd7c20292f9fff1f471fe573a8c4f9744183e1262c000fb5e38c0644e5a11427e3ddd9f034f50da497279bda238e7/?fname=%E5%B0%8F%E7%BC%98%20-%20%E8%A2%AB%E9%A9%AF%E6%9C%8D%E7%9A%84%E8%B1%A1.mp3","0tmo1zKXdKCSieZQAPbNWNNlAqDr-Ead0h2iXIkOdDEp1xAV3DJIi2M2fs70EwTN");*/
$news1 = array('bilibili动画','bilibili动画详细','http://wxtestsyb.ngrok.cc/mywx/image/1.jpg','www.bilibili.com');
$news2 = array('百度','图文消息描述','http://wxtestsyb.ngrok.cc/mywx/image/baidu.png','www.baidu.com');
//$weixin->replyNews($news1,$news2);
$data = $weixin->result();//微信客户端post来的xml数据包

$fromUserName = $data->{"FromUserName"};
//$weixin->replyText("asd");
/**
 * 若MsgType类型为文本
 */
if($data->{"MsgType"}=="text"){
    $data_Text=$data->{"Content"};//获取xml包中的content标签里的值
    if($data_Text =="姓名")
    $weixin->replyText("李兵");
    if($data_Text =="年龄")
         $weixin->replyText("22");
    if($data_Text =="单图文"){
        $weixin->replyNews($news1,$news2);
    }
    if($data_Text =="测试"){
        $weixin->replyText("http://wxtestsyb.ngrok.cc/mywx/consult.php
");
    }
    //$weixin->replyText($network->accessToken);
}
/**
 * 若MsgType类型为事件
 */
if($data->{"MsgType"}=="event"){
    //若事件为单机事件
    if($data->{"Event"}=="CLICK"){
        if($data->{"EventKey"}=="myInformation")
        {
            $sqlconn= new SqlFunction();
            //$fromUserName = 5;
            $sqlString = "SELECT * from student where stuOpenId='".$fromUserName."'";
            //$weixin->replyText($sqlString);
            $sqlResult=$sqlconn->excudSqlString($sqlString,WeiXinCheck::MSG_DATABASE);
            if($sqlResult->num_rows==1){
                //echo "数据库指令操作成功! ";
                while ($row = $sqlResult -> fetch_assoc()) {
                      $echostr= " 工号：". $row["stuNum"]. " \n 姓名：". $row["stuName"]." \n 专业：".$row['stuPro']." \n 班级：".$row['stuClass'];
                }
            }
            else{
                $sqlString = "SELECT * from teacher where techOpenId='".$fromUserName."'";
                $sqlResult=$sqlconn->excudSqlString($sqlString,WeiXinCheck::MSG_DATABASE);
                if($sqlResult->num_rows==1){
                //echo "数据库指令操作成功! ";
                while ($row = $sqlResult -> fetch_assoc()) {
                      $echostr= " 工号：". $row["techNumber"]. " \n 姓名：". $row["techName"];
                }
            }
            else{
                $echostr ="没有获取到数据";
            }
            $weixin->replyText($echostr);
            }
        }
        /*if($data->{"EventKey"}=="教师绑定"){
            $sqlcoon = new SqlFunction();
        }*/
        if($data->{"EventKey"}=="开始签到"){
            //生成ticket并返回一些参数
            $sqlconn = new SqlFunction();
            //根据openId获取老师工号
            //$fromUserName = 0;
            $sqlGetTeacherNumString = "SELECT * from teacher where techOpenId = '".$fromUserName."'";
            //var_dump($sqlGetTeacherNumString);die;
            //$weixin->replyText($sqlGetTeacherNumString);
            $sqlResultTeacherNum = $sqlconn->excudSqlString($sqlGetTeacherNumString,WeiXinCheck::MSG_DATABASE);
            if($sqlResultTeacherNum->num_rows == 0){ $weixin->replyText("用户信息异常,查看你的身份信息");}
            while ($row = $sqlResultTeacherNum -> fetch_assoc()) {
                $techNumber = $row["techNumber"];
            }
            $scene_id=$techNumber+time();//可以进行一些加密比如增加值或减少
            $result = $network->getTicketObject($scene_id);
            $result_ticket = $result->ticket;
            $result_expire_seconds = $result->expire_seconds;
            $result_url = $result->url;
            //签到开始时间
            $signStart = date("Y-m-d H:i:s",$scene_id-$techNumber);
            //获取当前星期几
            $week = date("N",$scene_id-$techNumber);
            //获取当前时间几点几分
            $nowTime = date("H:i",$scene_id-$techNumber);
            
            //$nowTime = "11:30";
            //$week = 3;
            //获取当前时间的几点几分对应的课程信息
            $sqlGetLessonInfo = "select * from lesson where '".$nowTime."' BETWEEN lessonStart and lessonStop and week =". $week." and techNumber = ".$techNumber;
            $sqlResultGeLessonInfo = $sqlconn->excudSqlString($sqlGetLessonInfo,WeiXinCheck::MSG_DATABASE);
            //$weixin->replyText($sqlGetLessonInfo);
            if($sqlResultGeLessonInfo->num_rows == 0){ $weixin->replyText("当前没有课程信息");}
            while ($row = $sqlResultGeLessonInfo -> fetch_assoc()) {
                $lessonName = $row["lessonName"];
                $lessonRoom = $row["lessonRoom"];
            }
            //$weixin->replyText($lessonName);
            //var_dump($signStart);
            //签到表数据库指令
            $sqlString = "INSERT into sign(scene_id,signStart,teacherNum,ticket,expire_seconds,lesson,week) VALUES ('".$scene_id."','".$signStart."','".$techNumber."','".$result_ticket."','".$result_expire_seconds."','".$lessonName."',".$week.")";
            //var_dump($sqlString);
            //$weixin->replyText($sqlString);
            $sqlResult=$sqlconn->excudSqlString($sqlString,WeiXinCheck::MSG_DATABASE);
            //var_dump($sqlResult);
            if($sqlResult==true){
                $userNametamp = $network->QRCode($result_ticket,$techNumber);
                $realPath = './image/QRCode/'.$userNametamp.'.jpg';
                $res = $network -> uploadfile($realPath);
                $weixin->replyImage($res["media_id"]);
            }else{
                $weixin->replyText("生成失败");
            }
        }
        if($data->{"EventKey"}=="结束签到"){
            //把有关这个老师的所有签到都结束，因为一个老师只存在一个教师。下课后二维码肯定失效
            //理论上要获取openid进行判定匹配
            //结束签到后把未签到的学生信息存入未签到学生信息表中
            //先根据openid获取教师的信息
            try
            {
                $sqlconn = new SqlFunction(); 
                //根据openId获取老师工号
                //$fromUserName = 0;
                $sqlGetTeacherNumString = "SELECT * from teacher where techOpenId = '".$fromUserName."'";

                $sqlResultTeacherNum = $sqlconn->excudSqlString($sqlGetTeacherNumString,WeiXinCheck::MSG_DATABASE);
                if($sqlResultTeacherNum->num_rows == 0){ $weixin->replyText("用户信息异常,查看你的身份信息");}
                while ($row = $sqlResultTeacherNum -> fetch_assoc()) {
                    $techNumber = $row["techNumber"];
                }
                //根据type = 1和老师的信息获取scene_id
                $sqlGetScene_id = "SELECT * from sign where type = 1 and teacherNum = ".$techNumber;

                $sqlResultGetScene_id= $sqlconn->excudSqlString($sqlGetScene_id,WeiXinCheck::MSG_DATABASE);
                 if($sqlResultGetScene_id->num_rows == 0){ $weixin->replyText("没有签到信息，请查阅是否发起签到");}
                while ($row = $sqlResultGetScene_id -> fetch_assoc()) {
                    $scene_id = $row["scene_id"];
                    $lesson =  $row["lesson"];
                    $week = $row["week"];
                }
                //根据scene_id获取签到的学生个数，并提示有哪些学生没签到
                //$weixin->replyText($techNumber);
                //然后根据教师信息，把和此教师工号有关的签到信息表中的type字段变为0
                $sqlStringChangeType = "UPDATE sign set type=0 where teacherNum = ".$techNumber ;
                $sqlResult=$sqlconn->excudSqlString($sqlStringChangeType,WeiXinCheck::MSG_DATABASE);
                if($sqlResult)
                {
                    //把学生信息分配,回复签到的学生个数，以及信息,并把type变为0防止重复或者冲突
                    $sqlGetStudentCountByScene_id = "SELECT * from signinsuccess where type = 1 and scene_id = ".$scene_id ." and week = ".$week ." and lesson = '".$lesson."'";
                    //$weixin->replyText($sqlGetStudentCountByScene_id);
                    $sqlResultGetStudentCountByScene_id = $sqlconn->excudSqlString($sqlGetStudentCountByScene_id,WeiXinCheck::MSG_DATABASE);
                    if($sqlResultGetStudentCountByScene_id->num_rows == 0){ $weixin->replyText("当前有0个人签到");}
                    $count = 0;
                    $stuSignSuccessNum='';
                    $stuClass = '';
                    while ($row = $sqlResultGetStudentCountByScene_id -> fetch_assoc()) {
                        $count ++ ;
                        $stuSignSuccessNum = $stuSignSuccessNum . $row["stuNum"];
                        $stuClass = $row["stuClass"];
                        if( $sqlResultGetStudentCountByScene_id->num_rows != $count)
                            $stuSignSuccessNum = $stuSignSuccessNum . ",\n";
                        else
                            $stuSignSuccessNum = $stuSignSuccessNum  . "。\n";
                    }

                    //获取未签到的学生信息
                    //有改动
                    $sqlGetsignfailStudent = "SELECT stuNum,stuName ,stuClass from student where  stuNum != (select signinsuccess.stuNum from signinsuccess,student where signinsuccess.stuNum = student.stuNum and signinsuccess.type = 1 and scene_id = ".$scene_id." ) and stuClass = (select signinsuccess.stuClass from signinsuccess,student where signinsuccess.stuNum = student.stuNum and signinsuccess.type = 1 and scene_id = " .$scene_id.")";
                    //$weixin->replyText($sqlGetsignfailStudent);
                    $sqlResultGetsignfailStudent = $sqlconn->excudSqlString($sqlGetsignfailStudent,WeiXinCheck::MSG_DATABASE);

                    if($sqlResultGetStudentCountByScene_id->num_rows == 0)
                    //$weixin->replyText($stuNum);
                        $weixin->replyText("已结束签到,班级为".$stuClass."\n课程:".$lesson."\n当前有".$sqlResultGetStudentCountByScene_id->num_rows."人签到,学号是".$stuSignSuccessNum."未签到人数为0,全部签到");
                    else
                        {
                            $count = 0;
                            $stuSignFailInfo ="";
                            while ($row = $sqlResultGetsignfailStudent -> fetch_assoc()) {
                                $count ++ ;
                                $stuSignFailInfo = $stuSignFailInfo ."学号:". $row["stuNum"] . "姓名: ".$row["stuName"];
                                if( $sqlResultGetsignfailStudent->num_rows != $count)
                                    $stuSignFailInfo = $stuSignFailInfo . ",\n";
                                else
                                    $stuSignFailInfo = $stuSignFailInfo  . "。\n";
                            }
                            //把签到失败的学生数据放入签到失败表
                            $sqlInsertStudentToFail = "INSERT INTO signfail(scene_id,stuNum,type,stuName,lesson,week,stuClass) select ".$scene_id.",stuNum,1,stuName,'".$lesson."',".$week." ,'".$stuClass."'from student where  stuNum != any (select signinsuccess.stuNum from signinsuccess,student where signinsuccess.stuNum = student.stuNum and signinsuccess.type = 1 and lesson= '".$lesson."')  and stuClass = ".$stuClass;
                            //$weixin->replyText($sqlInsertStudentToFail);
                            $sqlconn->excudSqlString($sqlInsertStudentToFail,WeiXinCheck::MSG_DATABASE);
                            $weixin->replyText("已结束签到,班级为".$stuClass."\n课程:".$lesson."\n当前有".$sqlResultGetStudentCountByScene_id->num_rows."人签到,学号是".$stuSignSuccessNum."未签到人数为".$count."\n学生信息为:\n".$stuSignFailInfo );

                        }

                }
                else
                    $weixin->replyText("接口异常");
            }
            catch(Exception $e)
            {
                $weixin->replyText("接口异常");
            }
        }
    }
    //若事件为关注事件
    if($data->{"Event"}=="subscribe"){
        $weixin->replyText("欢迎关注");
    }
    //若事件为取消关注事件
    if($data->{"Event"}=="unsubscribe"){
        $weixin->replyText("欢迎关注");
    }
    //若扫码成功后
    if($data->{"Event"}=="scancode_waitmsg"){
          echo "scancode_waitmsg";
        $weixin->replyText("scancode_waitmsg");
    }

    if($data->{"Event"}=="SCAN"){
        //在这里做判断获取数据库中的二维码ticket或者场景id
        $sqlconn = new SqlFunction();
        $sqlString = "SELECT * from sign where scene_id='".$data->{"EventKey"}."'and type=1";
        $sqlResult=$sqlconn->excudSqlString($sqlString,WeiXinCheck::MSG_DATABASE);
        if($sqlResult->num_rows>0){
            //echo "数据库指令操作成功! ";
            while ($row = $sqlResult -> fetch_assoc()) {
                $scene_id = $row["scene_id"];
                $signStart = $row["signStart"];
                $teacherNum = $row["teacherNum"];
                $expire_seconds = $row["expire_seconds"];
                $week = $row["week"];
                $lesson = $row["lesson"];
            }
            //限制签到时间，若二维码生成时间，即开始签到时间加上二维码有效期的时间戳大于当前时间则可以签到
            //但是一般性二维码有效期一过这个二维码也是失效了，所以定死十分钟有效期
            if(($scene_id - $teacherNum + 6000)>time())
            {
                $fromUserName = 0;
                $sqlGetstudentNumString = "SELECT * from student where stuOpenId = '" .$fromUserName."'";
                //$weixin->replyText($sqlGetstudentNumString);
                $sqlResultstudentNum = $sqlconn->excudSqlString($sqlGetstudentNumString,WeiXinCheck::MSG_DATABASE);

                if($sqlResultstudentNum->num_rows == 0){ $weixin->replyText("用户信息异常,查看你的身份信息");}
                while ($row = $sqlResultstudentNum -> fetch_assoc()) {
                    $stuNumber = $row["stuNum"];
                    $stuClass = $row["stuClass"];
                }
                //判断是否重复签到
                $sqlCount = "select count(*) from signinsuccess where scene_id = ".$scene_id ." and stuNum = ".$stuNumber;
                $sqlCountResult = $sqlconn->excudSqlString($sqlCount,WeiXinCheck::MSG_DATABASE);
                if($sqlCountResult){
                    while ($row = $sqlCountResult -> fetch_assoc()) {
                        $count = $row["count(*)"];
                    }
                }
                if($count>=1)
                    $weixin->replyText("您已经签过到，请勿重复签到");

                //把签到成功的学生信息录入
                $sqlInsertStudentToSuccess = "INSERT into signinsuccess(scene_id,signTime,stuNum,stuClass,week,lesson) VALUES ('".$scene_id."','".date("Y-m-d H:i:s", time())."','".$stuNumber."','".$stuClass."',".$week.",'".$lesson."')";
                //$weixin->replyText($sqlInsertStudentToSuccess);
                $sqlInsertResult=$sqlconn->excudSqlString($sqlInsertStudentToSuccess,WeiXinCheck::MSG_DATABASE);
                //var_dump($sqlResult);
                if($sqlInsertResult==true){
                    $weixin->replyText("签到成功");
                }else{
                    $weixin->replyText("签到失败");
                }
              
            }
            else
                $weixin->replyText("签到失败,已经结束签到或者二维码超时");
        }
        else
            $weixin->replyText("签到失败,已经结束签到或者二维码超时");
    }
}
else{
    $weixin->replyText("请输入正确参数");
}
