<?php
class ResultCode{
	public $errorCode;
	public $errorMessage;
	public $amount = 0;
}

class ContentResponse {

	public static function parserResult($message){
		$result = new ResultCode();
		$array_message = explode("|",$message);
		$result->errorCode = $array_message[0];
		$result->errorMessage = $array_message[1];
		$result->amount = isset($array_message[2])?$array_message[2]:0;
		return $result;
	}

	public static function getResultCode(){
		return new ResultCode();
	}
	
}