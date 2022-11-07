<?php

return array(
	'kd_tips1'=>array(
		'title'=>'<div style="color:blue;">'.lang('thirdlogin_config1').'</div>',
		'type'=>'hidden',
		'value'=>''
	),
	'thirdTypes'=>array(
		'title'=>lang('thirdlogin_config2'),
		'type'=>'checkbox',
		'options'=>array(
			'qq'=>lang('thirdlogin_config3'),	
			'weixin'=>lang('thirdlogin_config4'),
			'weibo'=>lang('thirdlogin_config5'),
			'app_qq'=>lang('thirdlogin_config6'),
			'app_weixin'=>lang('thirdlogin_config7'),
			'app_alipay'=>lang('thirdlogin_config8'),
			'facebook'=>lang('thirdlogin_config_fb'),
			'google'=>lang('thirdlogin_config_google'),
		),
		'value'=>'',
	),
	'group'=> array(
		'type'=>'group',
		'options'=>array(
			'qq'=>array(
				'title'=>lang('thirdlogin_config3'),
				'options'=>array(
					'qq_tips'=>array(
						'title'=>'<div style="color:blue;">'.lang('thirdlogin_config9').'：
						<div style="line-height:20px;">http://doamin/index.php/addon/thirdlogin-thirdlogin-qqcallback.html;<br/>http://doamin/addon/thirdlogin-thirdlogin-qqcallback.html;</div>
						<div style="line-height:20px;">http://doamin/index.php/addon/thirdlogin-thirdlogin-mobileqqcallback.html;<br/>http://doamin/addon/thirdlogin-thirdlogin-mobileqqcallback.html;</div></div>
						<div style="color:red;">'.lang('thirdlogin_config10').'</div>',
						'type'=>'hidden',
						'value'=>'',
						'tip'=>''
					),
					'appId_qq'=>array(
						'title'=>lang('thirdlogin_config11'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					),
					'appKey_qq'=>array(
						'title'=>lang('thirdlogin_config12'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					),
					'img_qq'=>array(
						'type'=>'hidden',
						'value'=>'/addons/thirdlogin/view/images/qq.png',
						'tip'=>''
					)
				)
			),
			'weixin'=>array(
				'title'=>lang('thirdlogin_config4'),
				'options'=>array(
					'appId_weixin'=>array(
						'title'=>lang('thirdlogin_config13'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					),
					'appKey_weixin'=>array(
						'title'=>lang('thirdlogin_config14'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					),
					'img_weixin'=>array(
						'type'=>'hidden',
						'value'=>'/addons/thirdlogin/view/images/weixin.png',
						'tip'=>''
					)
				)
			),
			'weibo'=>array(
				'title'=>lang('thirdlogin_config15'),
				'options'=>array(
					'appId_weibo'=>array(
						'title'=>lang('thirdlogin_config16'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					),
					'appKey_weibo'=>array(
						'title'=>lang('thirdlogin_config17'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					),
					'img_weibo'=>array(
						'type'=>'hidden',
						'value'=>'/addons/thirdlogin/view/images/weibo.png',
						'tip'=>''
					)
				)
			),
			'facebook'=>array(
				'title'=>lang('thirdlogin_config21'),
				'options'=>array(
					'appId_facebook'=>array(
						'title'=>lang('thirdlogin_config22'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					),
					'appKey_facebook'=>array(
						'title'=>lang('thirdlogin_config23'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					),
					'img_facebook'=>array(
						'type'=>'hidden',
						'value'=>'/addons/thirdlogin/view/images/facebook.png',
						'tip'=>''
					)
				)
			),
			'google'=>array(
				'title'=>lang('thirdlogin_config24'),
				'options'=>array(
					'appId_google'=>array(
						'title'=>lang('thirdlogin_config25'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					),
					'appKey_google'=>array(
						'title'=>lang('thirdlogin_config26'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					),
					'img_google'=>array(
						'type'=>'hidden',
						'value'=>'/addons/thirdlogin/view/images/google.png',
						'tip'=>''
					)
				)
			),
			'app_alipay'=>array(
				'title'=>lang('thirdlogin_config18'),
				'options'=>array(
					'parentId'=>array(
						'title'=>lang('thirdlogin_config19'),
						'type'=>'text',
						'value'=>'',
						'tip'=>''
					)
				)
			)
		)
	)
);