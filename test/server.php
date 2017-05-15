<?php   
    if (isset($_POST['action']))  
    {  

        switch($_POST['action'])  
        {  
            case "btn1":btn1();break;  
            case "btn2":btn2();break;  
            default:break;  
        }  
    }  
    if(isset($_POST['user']))
        submit($_POST['user']);

    function submit($user){
        echo $user;
    }

    function btn1()  
    {  
        echo "hello 按钮1";  
    }  
    function btn2()  
    {  
        echo "hello 按钮2";  
    }  
  
?>  