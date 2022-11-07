<?php
function WSTCaptcha($isApp=0){
    $size = 20;
    //字体类型，本例为宋体
    $font = Env::get('root_path')."addons/captcha/resource/ttfs/SourceHanSerifCN-Medium.otf";
    //显示的文字
    $text = session('addon_captcha_text');
    $width = 120;
    $height = 50;
    //创建一个长为500高为80的空白图片
    $img = imagecreate($width, $height);
    //给图片分配颜色
    $bg_color = imagecolorallocate($img, 250, 250, 250);

    // $codeSet = '2345678abcdefhijkmnpqrstuvwxyz';
    // for($i = 0; $i < 5; $i++){
    //     //杂点颜色
    //     $noiseColor = imagecolorallocate($img, 120, 120, 120);
    //     for($j = 0; $j < 5; $j++) {
    //         // 绘杂点
    //         imagestring($img, 5, mt_rand(-10, $width),  mt_rand(-10, $height), $codeSet[mt_rand(0, 29)], $noiseColor);
    //     }
    // }
    $px = $py = 0;
    $xcolor = imagecolorallocate($img, 0, 0, 0);    
    // 曲线前部分
    $A = mt_rand(1, $height/2);                  // 振幅
    $b = mt_rand(-$height/4, $height/4);   // Y轴方向偏移量
    $f = mt_rand(-$height/4, $height/4);   // X轴方向偏移量
    $T = mt_rand($height, $width*2);  // 周期
    $w = (2* M_PI)/$T;
                        
    $px1 = 0;  // 曲线横坐标起始位置
    $px2 = mt_rand($width/2, $width * 0.8);  // 曲线横坐标结束位置

    for ($px=$px1; $px<=$px2; $px = $px + 1) {
        if ($w!=0) {
            $py = $A * sin($w*$px + $f)+ $b + $height/2;  // y = Asin(ωx+φ) + b
            $i = (int) ($size/5);
            while ($i > 0) {    
                imagesetpixel($img, $px + $i , $py + $i, $xcolor);  // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多               
                    $i--;
            }
        }
    }
        
    // 曲线后部分
    $A = mt_rand(1, $height/2);                  // 振幅        
    $f = mt_rand(-$height/4, $height/4);   // X轴方向偏移量
    $T = mt_rand($height, $width*2);  // 周期
    $w = (2* M_PI)/$T;      
    $b = $py - $A * sin($w*$px + $f) - $height/2;
    $px1 = $px2;
    $px2 = $width;

    for ($px=$px1; $px<=$px2; $px=$px+ 1) {
        if ($w!=0) {
            $py = $A * sin($w*$px + $f)+ $b + $height/2;  // y = Asin(ωx+φ) + b
             $i = (int) ($size/5);
            while ($i > 0) {            
                imagesetpixel($img, $px + $i, $py + $i, $xcolor);    
                $i--;
            }
        }
    }
    
    $a = imagettfbbox($size, 0, $font, $text);   //得到字符串虚拟方框四个点的坐标
    $len = $a[2] - $a[0];
    $x = ($width - $len) / 2;
    $y = $height / 2 + ($a[3] - $a[5]) / 2;
    //设置字体颜色
    $black = imagecolorallocate($img, 0, 0, 0);
    //将ttf文字写到图片中
    imagettftext($img, $size, mt_rand(-10,10), $x, $y, $black, $font, $text);
    //发送头信息
    header('Content-Type: image/png');
    if($isApp==1){
        $filePath = '/upload/captcha/captcha.png';
        imagepng($img,WSTRootPath().$filePath);
        return WSTDomain().$filePath;
    }
    //输出图片
    imagepng($img);
    imagedestroy($img);
}