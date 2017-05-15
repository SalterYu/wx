<?php
class WeixinApi
{
	public function valid()
	{
		if($this->checkSingnature())
		{
			echo $_GET['echostr'];
		}
		else
		{
			echo "Error";
		}
	}	
	private function checkSingnature()
	{		
		$signature =$_GET['signature'];
		$timestamp=$_GET['timestamp'];
		$nonce=$_GET['nonce'];
		$tmpArr=array(TOKEN,$timestamp,$nonce);
		sort($tmpArr);
		$tmpStr=sha1(implode($tmpArr));
		if($signature==$tmpStr)
		{	
			return true;	
		}
		else
		{
			return false;	
		}
    }
}

