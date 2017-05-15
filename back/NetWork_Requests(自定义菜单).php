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

    public function createMenu($data){
        $url = "{$this->wechatApiBase}/menu/create";
        $param = array('access_token'=>$this->accessToken);
        //var_dump($param);die;
        $post_param['button'] = $data;
        array_walk_recursive($post_param,function(&$value){
            $value = urlencode($value);
        });
        $post_param = urldecode(json_encode($post_param));
        
        $res = self::http($url,$param,$post_param,'POST');

        return json_decode($res,true);
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
        //return self::http($url,$param);
        $res = self::http($url,$param);
        $access = json_decode($res,true);
        if(!empty($access)){
            $this->accessToken = $access['access_token'];
        }else{
            throw new Exception("AccessToken获取失败", 1);
            
        }

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
}

$menu = array(
    array(
        'type' =>'click',
        'name' =>'官网',
        'key' =>'官网'
    ),
    array(
        'name'=>'博客',
        'sub_button'=>array(
            array(
                 'type' =>'view',
                 'name' =>'哔哩哔哩动画',
                 'url' =>'http://www.bilibili.com'
            ),
            array(
                 'type' =>'view',
                 'name' =>'百度',
                 'url' =>'http://www.baidu.com'
            ),
            array(
                 'type' =>'view',
                 'name' =>'微信开发文档',
                 'url' =>'https://mp.weixin.qq.com/wiki'
            ),
        )
    ),
    array(
        'type'=>'scancode_push',
        'name'=>'扫一扫',
        'key'=>'扫一扫'
        ),
);

 //$test = http('https://www.baidu.com');
//$network = new NetWork_Requests();
$network = new NetWork_Requests('wx5bf1351ba19ec858','e78fdc44ce5a4350023d0c20c7150d89');
//echo $network->getAccessToken();
$network -> getAccessToken();
$res = $network->createMenu($menu);
echo "<pre>";
print_r($res);
/*die;
$s = '{
     "button":[
     {  
          "type":"click",
          "name":"今日歌曲",
          "key":"V1001_TODAY_MUSIC"
      },
      {
           "name":"菜单",
           "sub_button":[
           {    
               "type":"view",
               "name":"搜索",
               "url":"http://www.soso.com/"
            },
            {
               "type":"view",
               "name":"视频",
               "url":"http://v.qq.com/"
            },
            {
               "type":"click",
               "name":"赞一下我们",
               "key":"V1001_GOOD"
            }]
       }]
 }';
 echo "<pre>";
 print_r(json_decode($s));
*/