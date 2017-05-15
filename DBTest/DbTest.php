<?php
//php连接MySQLI
$link = mysqli_connect('localhost','user','password','world');
if (!$link){
printf("不能连接到MySQL. 错误代码: %sn", mysqli_connect_error());
exit;
}
/*发送查询指令*/
if ($result = mysqli_query($link, 'SELECT Name, Population FROM City ORDER BY Population DESC LIMIT 5')){
print("Very large cities are:n");
//开始获取查询结果
while( $row = mysqli_fetch_assoc($result) ){
printf("%s (%s)n", $row['Name'], $row['Population']);
}
mysqli_free_result($result);//注销结果集。释放内存
}
mysqli_close($link); //关闭连接
?>
