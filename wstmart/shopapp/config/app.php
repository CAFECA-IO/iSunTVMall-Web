<?php
return [
	'app_debug'              => true,
    'exception_handle'       => function($e) {

      $data =  json_encode(["status"=>-10000,"msg"=>"{$e->getMessage()}"]);
      die($data);
      var_dump('我进入到app的捕获异常中~');
      var_dump($_SERVER);
      $data = $_SERVER;
      var_dump(get_class_methods($e));
      var_dump($e->getMessage());
	  // 参数验证错误
	  if ($e instanceof \think\exception\ValidateException) {
	  	die('进入判断1');
	  	return json($e->getError(), 422);
	  }

	  // 请求异常
	  if ($e instanceof \think\exception\HttpException && request()->isAjax()) {
	  	die('进入判断2');
	  	return response($e->getMessage(), $e->getStatusCode());
	  }    
	},
];
