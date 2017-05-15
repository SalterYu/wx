<?php
class WeiXinCheck{
    const MSG_TYPE_TEXT = 'text';
    const MSG_TYPE_IMAGE = 'image';
    const MSG_TYPE_VOICE ='voice';
    const MSG_TYPE_VIEDO = 'video';
    const MSG_TYPE_MUSIC = 'music';
    const MSG_TYPE_NEWS = 'news';
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
        }
        $this->result();
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
        /*$postObj =  simplexml_load_string($postString,'SimpleXMLElement',LIBXML_NOCDATA);
        $this->data = $postObj;*/
        $xml = simplexml_load_string($postString,'SimpleXMLElement',LIBXML_NOCDATA);
        foreach ($xml as $key => $value) {   //强制转换防止乱码
            $this->data[$key] = strval($value);
        }
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

$weixin = new WeiXinCheck("weixin");
//echo "<pre>";
//print_r($weixin->replyText("dd")); //文字回复
//print_r($weixin->replyImage("udrDpCeKGR61qOiAN8L20YFOXDpEQeRb56W8cA4xCYwGiI5wX5xbxncSgQ6OZhBA"));//图片回复
//print_r($weixin->replyVoice("MMhMsG1O52OM6etF9lT0SFuCsC8896PnUkF1B7Rg7ic2WoWMOGEZwd_fd22sgGNV")); //音频回复
//print_r($weixin->replyVideo("1EsShoFL-ECxRQJNMqA4AQsVao2LjZ7XPBwZj1miqdediBeftzjbmBcIv0A02sQS","测试视频","测试")); //视频回复有错误
/*$weixin->replyMusic("音乐标题","音乐描述","
http://sh-ctfs.ftn.qq.com/ftn_handler/eb6047a7da16feda55b6ccece12014733b1fd7c20292f9fff1f471fe573a8c4f9744183e1262c000fb5e38c0644e5a11427e3ddd9f034f50da497279bda238e7/?fname=%E5%B0%8F%E7%BC%98%20-%20%E8%A2%AB%E9%A9%AF%E6%9C%8D%E7%9A%84%E8%B1%A1.mp3","
http://sh-ctfs.ftn.qq.com/ftn_handler/eb6047a7da16feda55b6ccece12014733b1fd7c20292f9fff1f471fe573a8c4f9744183e1262c000fb5e38c0644e5a11427e3ddd9f034f50da497279bda238e7/?fname=%E5%B0%8F%E7%BC%98%20-%20%E8%A2%AB%E9%A9%AF%E6%9C%8D%E7%9A%84%E8%B1%A1.mp3","0tmo1zKXdKCSieZQAPbNWNNlAqDr-Ead0h2iXIkOdDEp1xAV3DJIi2M2fs70EwTN");*/
$news1 = array('bilibili动画','bilibili动画详细','http://wxtestsyb.ngrok.cc/mywx/image/1.jpg','www.bilibili.com');
$news2 = array('百度','图文消息描述','http://wxtestsyb.ngrok.cc/mywx/image/baidu.png','www.baidu.com');
$weixin->replyNews($news1,$news2);
