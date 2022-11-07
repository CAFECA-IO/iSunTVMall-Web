function login(){
    var public_key=$('#token').val();
    var exponent="10001";
    var res = '';
    if(WST.conf.IS_CRYPT=='1'){
   	    var rsa = new RSAKey();
        rsa.setPublic(public_key, exponent);
        var res = rsa.encrypt($.trim($('#loginPwd').val()));
    }else{
        res = $.trim($('#loginPwd').val());
    }
    var loading = WST.msg(WST.lang('loading'), {icon:16,time:60000,offset:'200px'});
	var params = WST.getParams('.ipt');
	params.typ = 2;
	params.loginPwd = res;
	$.post(WST.U('store/index/checkLogin'),params,function(data,textStatus){
		layer.close(loading);
		var json = WST.toJson(data);
		if(json.status=='1'){
			WST.msg(json.msg,{icon:1,offset: '200px'},function(){
				if(parent){
					parent.location.href=WST.U('store/index/index');;
				}else{
                    location.href=WST.U('store/index/index');
				}
			});
		}else{
			getVerify('#verifyImg');
			WST.msg(json.msg,{icon:2,offset: '200px'});			
		}
	});
}
var getVerify = function(img){
	$(img).attr('src',WST.U('store/index/getVerify','rnd='+Math.random()));
	$('#verifyCode').val('');
}
$(document).keypress(function(e) { 
	if(e.which == 13) {  
		login();  
	} 
}); 
