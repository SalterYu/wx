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
     * 创建自定义菜单
     * @param  [object] $data [菜单数组]
     * @return [xml]       [微信返回结果]
     */
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

    public function deleteMenu(){
         $url = "{$this->wechatApiBase}/menu/delete";
         $param=array('access_token'=>$this->accessToken);
         $res = self::http($url,$param);
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
            //var_dump($this->accessToken);die;
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
    array(    //教师菜单
        'name' => '教师',
        'sub_button'=>array(
            array(
                'type' =>'click',
                'name' =>'开始签到',
                'key' =>'开始签到'
            ),
            array(
                'type' =>'click',
                'name' =>'结束签到',
                'key' =>'结束签到'
            ),
            array(
                'type' =>'view',
                'name' =>'教师绑定',
                'url' =>"http://wxtestsyb.ngrok.cc/mywx/content/techbind.php"
            ),
            array(
                'type' =>'view',
                'name' =>'查阅出勤',
                'url' =>"http://wxtestsyb.ngrok.cc/mywx/content/select.php"
            ),
            array(
                'type' =>'view',
                'name' =>'添加课程',
                'url' =>"http://wxtestsyb.ngrok.cc/mywx/content/lesson.php"
            ),
        )
    ),
    array(    //学生菜单
        'name' => '学生',
        'sub_button'=>array(
            array(
                'type' =>'scancode_push',
                'name' =>'签到',
                'key' =>'签到',
                'sub_button'=>[]
            ),
             array(
                "type"=>"scancode_waitmsg", 
                'name' =>'签到waitmsg',
                'key' =>'签到waitmsg',
                'sub_button'=>[]
            ),

            /*array(
                'type' =>'click',
                'name' =>'课程查询',
                'key' =>'课程查询'
            ),*/
            array(
                'type' =>'view',
                'name' =>'学生绑定',
                'url' =>"http://wxtestsyb.ngrok.cc/mywx/content/stubind.php"
            ),
            /*array(
                'type' =>'click',
                'name' =>'查看作业',
                'key' =>'查看作业'
            ),
            array(
                'type' =>'click',
                'name' =>'提问',
                'key' =>'提问'
            ),*/
        ),
    ),
    array(    //个人信息
        'name' => '其他功能',
        'sub_button'=>array(
            /*array(
                'type' =>'click',
                'name' =>'关键词参数帮助',
                'key' =>'help'
            ),*/
            array(
                'type' =>'click',
                'name' =>'我的信息',
                'key' =>'myInformation'
            ),
            array(
                'type' =>'view',
                'name' =>'辅导员统计',
                'url' =>"http://wxtestsyb.ngrok.cc/mywx/content/statistics.php"
            ),
        ),
    )
);

 //$test = http('https://www.baidu.com');
//$network = new NetWork_Requests();
$network = new NetWork_Requests('wx5bf1351ba19ec858','e78fdc44ce5a4350023d0c20c7150d89');
//echo $network->getAccessToken();
$network -> getAccessToken();
$res = $network->createMenu($menu);
//$res = $network->deleteMenu();
echo "<pre>";
print_r($res);
