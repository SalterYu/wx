<?php
//  获取accessToken
class NetWork_Requests{
    private $appid = '';
    private $appsecret = '';
    public $accessToken = '';
    //高级接口Api根地址
    private $wechatApiBase = 'https://api.weixin.qq.com/cgi-bin';


    public function __construct($appid='',$appsecret='')
    {
        $this->appid = $appid;
        $this->appsecret = $appsecret;
    }

    /**
     * 获取accessToken
     * @return array 请求之后得到的数组
     */
    public function getAccessToken(){
        $url = "{$this->wechatApiBase}/token";
        $param = array(
            'grant_type' => 'client_credential',
            'appid'=> $this->appid,
            'secret'=> $this->appsecret
        );

        $res= self::http($url,$param);
        $access = json_decode($res,true);
        if(!empty($access)){
            $this->accessToken = $access['access_token'];
            //$var_dump($this->accessToken);die;
            //var_dump($this->accessToken);die;
            //return $access['access_token'];
        }else{
            throw new Exception("AccessToken获取失败", 1);
            
        }
        return $access;
    }

    /**
     * 网络请求方法
     * @param  string $url 请求url
     * @param  string $param GET请求参数
     * @param  string $data POST请求参数
     * @param  string $method 请求方式
     * @return miexid 网络请求返回的数据
     */
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

     //获取二维码ticket的post方法
     public function https_post($url,$data = null){
        $curl=curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        if(!empty($data)){
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output=curl_exec($curl);
        curl_close($curl);
        return $output;
     }

     /**
      * 获取Ticket的object包括ticket，expire_seconds和二维码解析结果的url
      * @param  string $action_name    [是否临时二维码QR_SCENE表示临时，QR_LIMIT_STR_SCENE表示永久]
      * @param  string $scene_id       [临时二维码的场景id]
      * @param  string $expire_seconds [该二维码有效时间秒为单位]
      * @return [object]                 [ticket的object信息]
      */
     function getTicketObject($scene_id='',$action_name='QR_SCENE',$expire_seconds='3600'){
        //1.组织url
        $url = 'api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->accessToken;
        //2.携带参数去请求
        //判断是临时的还是永久的
        if($action_name =='QR_SCENE'){
            //临时二维码的ticket的值发送参数
            $data = '{"expire_seconds": '.$expire_seconds.', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
        }else{
            $data = '{"action_name": "'.$action_name.'", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
        }
        $content = $this->https_post($url,$data);
        $result = json_decode($content);
        //var_dump($result);
        return $result;
     }

     /**
      * 换取二维码，并保存在服务器中，命名格式老师名加时间戳
      */
     public function QRCode($ticket,$userName='teacherName'){
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
        $content = $this->http($url);//content为图片一张
        //var_dump($content);
        //$dt = new DateTime();
        //$now = $dt->format('Y-m-d H:i:s');
        $timeDamp = time();
        $str = $userName . '-'.$timeDamp ;
        //处理返回值
        file_put_contents('./image/QRCode/'.$str.'.jpg', $content);
        return $str;
     }

     /**
      * 新增临时素材
      * @param  [string] $file [需要上传的文件名]
      * @param  string $type [上传文件类型]
      * @return [array]       [上传之后的反馈信息]
      */
     public function uploadfile($file,$type='image'){
        $param= array(
            'access_token'=>$this->accessToken,
            'type'=>$type,
            );
        //$file = array('media'=>"@{$file}");//双引号解析变量,由于PHP>5的版本弃用了curl_setopt_array来Post file的方法所以使用下面的CURLFile方法
        $file = array('media'=>new \CURLFile(realpath($file))); 
        //var_dump($file);die;
        $url = "{$this->wechatApiBase}/media/upload";
        $res = self::http($url,$param,$file,'POST');
        return json_decode($res,true);
     }

     /**
      * 生成随机字符串，长度八位作为二维码图片的名字
      * @param  [type] $length [description]
      * @return [type]         [description]
      */
     public function getRandChar($length=8){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$Wlength;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
     }
}

//S使用方法
 //$test = http('https://www.baidu.com');
//$network = new NetWork_Requests();
//$network = new NetWork_Requests('wx5bf1351ba19ec858','e78fdc44ce5a4350023d0c20c7150d89'); //初始化network，参数为appid和secret
//$network -> getAccessToken();//获取accessToken并初始化accessToken
//方法1 由于PHP5以上弃用了curl_setopt_array()上传图片
/*$path = 'mywx/image/QRCode/teacher-1489232673.jpg';
$img = $_SERVER['DOCUMENT_ROOT'].'mywx/image/QRCode/teacher-1489232673.jpg';
$res = $network->uploadfile($img);*/
//
//$result = $network->getTicketObject(); //获取二维码ticket对象，其中有三个参数ticket，解析后的url，和有效时间
//$userNametamp = $network->QRCode($result->ticket);//把二维码变成图片后保存到本地并上传到临时素材库，返回图片名字
//$realPath = './image/QRCode/'.$userNametamp.'.jpg';//定义真实路劲
//var_dump($realPath);
//$res = $network -> uploadfile($realPath);//获取上传图片后微信返回的消息数组，有type=image，media_id和创建时间，主要是要media_id
//var_dump($res);

