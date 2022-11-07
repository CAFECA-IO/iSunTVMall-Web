var isShopUploadinit = false;
function toEdit(type){
	$('#einfo_'+type).show();
	$('#vinfo_'+type).hide();
	$('#einfo_'+type).removeClass('hide');
    if(!isShopUploadinit){
    	isShopUploadinit = true;
    	WST.upload({
	  	  pick:'#shopImgPicker',
	  	  formData: {dir:'shops',isThumb:1},
	  	  accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
	  	  callback:function(f){
	  		  var json = WST.toJson(f);
	  		  if(json.status==1){
	  			$('#uploadMsg').empty().hide();
	            $('#preview').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb);
	            $('#shopImg').val(json.savePath+json.name);
	  		  }
		  },
		  progress:function(rate){
		      $('#uploadMsg').show().html(WST.lang('upload_rate')+rate+"%");
		  }
	    });
	    initTime('#serviceStartTime',$('#serviceStartTime').attr('v'));
	    initTime('#serviceEndTime',$('#serviceEndTime').attr('v'));
    }
}
function initTime($id,val){
	var html = [],t0,t1;
	var str = val.split(':');
	for(var i=0;i<24;i++){
		t0 = (val.indexOf(':00')>-1 && (parseInt(str[0],10)==i))?'selected':'';
		t1 = (val.indexOf(':30')>-1 && (parseInt(str[0],10)==i))?'selected':'';
		html.push('<option value="'+i+':00" '+t0+'>'+i+':00</option>');
		html.push('<option value="'+i+':30" '+t1+'>'+i+':30</option>');
	}
	$($id).append(html.join(''));
}
function toCancel(type){
	$('#einfo_'+type).hide();
	$('#vinfo_'+type).show();
}

function editInfo(){
	$('#editFrom_1').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt_1');
			var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		    $.post(WST.U('shop/shops/editInfo'),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toJson(data);
		    	if(json.status=='1'){
		    		WST.msg(WST.lang('op_ok'),{icon:1});
		    		$('#v_shopImg').attr('src',WST.conf.RESOURCE_PATH+"/"+params.shopImg);
		    		
		    		$('#v_shopAtive').html((params.shopAtive==1)?WST.lang('in_business'):WST.lang('resting'));
		    		if(params.isInvoice==1){
		    			$('#tr_isInvoice').show();
                        $('#v_isInvoice').html(WST.lang('provide_invoice'));
		    		}else{
		    			$('#tr_isInvoice').hide();
		    			$('#v_isInvoice').html(WST.lang('no_invoice'));
		    		}
		    		$('#v_serviceStartTime').html(params.serviceStartTime);
		    		$('#v_serviceEndTime').html(params.serviceEndTime);
		    		$('#einfo_1').hide();
	                $('#vinfo_1').show();
		    	}else{
		    		WST.msg(json.msg,{icon:2});
		    	}
		    });
		}
	});
}

