<?php

return array(
    'tips'=>array(
        'title'=>lang('txlive_config_tips1',['<a href="https://cloud.tencent.com" target="_blank" style="font-weight:bold;">','</a>']),
        'type'=>'hidden',
        'value'=>''
    ),
    'tips2'=>array(
        'title'=>lang('txlive_config_tips2'),
        'type'=>'hidden',
        'value'=>''
    ),
    'tips3'=>array(
        'title'=>lang('txlive_config_tips3'),
        'type'=>'hidden',
        'value'=>''
    ),
    'tips4'=>array(
        'title'=>lang('txlive_config_tips4'),
        'type'=>'hidden',
        'value'=>''
    ),
    'pushDomain'=>[
        'title' => lang('txlive_push_domain'),
        'type' => 'text',
        'tips' => '',
        'value' => ''
    ],
    'liveDomain'=>[
        'title' => lang('txlive_live_domain'),
        'type' => 'text',
        'tips' => '',
        'value' => ''
    ],
    'pushKey'=>[
        'title' => lang('txlive_push_key'),
        'type' => 'text',
        'tips' => '',
        'value' => ''
    ],
    'liveKey'=>[
        'title' => lang('txlive_live_key'),
        'type' => 'text',
        'tips' => '',
        'value' => ''
    ],
    'validTime'=>[
        'title' => lang('txlive_live_key_valid_time'),
        'type' => 'text',
        'tips' => '',
        'value' => '0'
    ],
    'licenceUrl'=>[
        'title' => 'licenceUrl',
        'type' => 'text',
        'tips' => '',
        'value' => '0'
    ],
    'licenceKey'=>[
        'title' => 'licenceKey',
        'type' => 'text',
        'tips' => '',
        'value' => '0'
    ],
    'liveReplay'=>array(
        'title'=>lang("txlive_replay"),
        'type'=>'radio',
        'options'=>array(
            '1'=>lang('txlive_open'),
            '0'=>lang('txlive_close')
        ),
        'value'=>'0',
    ),
    'secretId'=>[
        'title' => 'secretId',
        'type' => 'text',
        'tips' => '',
        'value' => '0'
    ],
    'secretKey'=>[
        'title' => 'secretKey',
        'type' => 'text',
        'tips' => '',
        'value' => '0'
    ],
    'liveIM'=>array(
        'title'=>lang("txlive_im"),
        'type'=>'radio',
        'options'=>array(
            '1'=>lang('txlive_open'),
            '0'=>lang('txlive_close')
        ),
        'value'=>'0',
    ),
    'IMAppID'=>[
        'title' => 'IMAppID',
        'type' => 'text',
        'tips' => '',
        'value' => '0'
    ],
    'IMSecretKey'=>[
        'title' => 'IMSecretKey',
        'type' => 'text',
        'tips' => '',
        'value' => '0'
    ],
    'IMNotice'=>[
        'title' => lang('txlive_im_notice'),
        'type' => 'text',
        'tips' => '',
        'value' => '0'
    ],
);
