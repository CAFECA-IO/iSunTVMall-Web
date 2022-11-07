$(function () {
	$('#tab').TabPanel({tab:0,callback:function(tab){
		switch(tab){
		   case 0:pageQuery(0);break;
		   case 1:pageConfigQuery(0);break;
		}	
	}})
});
var isSetPayPwd = 1;
function getUserMoney(){
	$.post(WST.U('home/users/getUserMoney'),{},function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1){
			var userMoney = json.data.userMoney;
			var rechargeMoney = json.data.rechargeMoney;
			$('#userMoney').html(WST.lang('currency_symbol')+userMoney);
			$('#lockMoney').html(WST.lang('currency_symbol')+json.data.lockMoney);
			rechargeMoney = parseFloat(userMoney - rechargeMoney)
			$('#userCashMoney').html(WST.lang('currency_symbol')+rechargeMoney.toFixed(2));
			if(json.data.isDraw==1){
               $('#drawBtn').show();
			}else{
               $('#drawBtn').hide();
			}
			isSetPayPwd = json.data.isSetPayPwd;
		}
	});
}
function pageQuery(p){
	var tips = WST.load({msg:WST.lang('loading_tips')});
	var params = {};
	params.page = p;
	$.post(WST.U('home/cashdraws/pageQuery'),params,function(data,textStatus){
		layer.close(tips);
	    var json = WST.toJson(data);
	    if(json.status==1){
	    	json = json.data;
		    var gettpl = document.getElementById('draw-list').innerHTML;
		    laytpl(gettpl).render(json.data, function(html){
		       	$('#draw-page-list').html(html);
		    });
		    if(json.last_page>1){
		       	laypage({
			        cont: 'draw-pager', 
			        pages:json.last_page, 
			        curr: json.current_page,
			        skin: '#e23e3d',
			        groups: 3,
			        jump: function(e, first){
			        	if(!first){
			        		pageQuery(e.curr);
			        	}
			        } 
			    });
		     }else{
		       	 $('#draw-pager').empty();
		     }
	    }
	});
}
var w;
function toDrawMoney(){
	if(isSetPayPwd==0){
		WST.msg(WST.lang('please_set_pay_password'),{icon:2},function(){
			location.href = WST.U('home/users/security');
		});
		return;
	}
    var tips = WST.load({msg:WST.lang('loading_tips')});
	$.post(WST.U('home/cashdraws/toEdit'),{},function(data,textStatus){
		layer.close(tips);
		w = WST.open({
		    type: 1,
		    title:WST.lang('drawal_apply'),
		    shade: [0.6, '#000'],
		    border: [0],
		    content: data,
		    area: ['550px', '350px'],
		    offset: '100px'
		});
	});
}
function drawMoney(){
	$('#drawForm').isValid(function(v){
		if(v){
			var params = WST.getParams('.j-ipt');
			if(params.accType==1 && $.trim(params.accUser)==''){
                WST.msg(WST.lang('require_true_name'),{icon:2,time:3000});
                return;
			}
		    if(window.conf.IS_CRYPT=='1'){
		        var public_key=$('#token').val();
		        var exponent="10001";
		   	    var rsa = new RSAKey();
		        rsa.setPublic(public_key, exponent);
		        params.payPwd = rsa.encrypt(params.payPwd);
		    }
			var tips = WST.load({msg:WST.lang('submiting_tips')});
			$.post(WST.U('home/cashdraws/drawMoney'),params,function(data,textStatus){
				layer.close(tips);
			    var json = WST.toJson(data);
			    if(json.status==1){
		            WST.msg(json.msg,{icon:1},function(){
		            	pageQuery(0);
		            	getUserMoney();
		            	layer.close(w);
		            });
			    }else{
			    	WST.msg(json.msg,{icon:2,time:3000});
			    }
			});
		}
	});
}
function layerclose(){
  layer.close(w);
}

function pageConfigQuery(p){
	var tips = WST.load({msg:WST.lang('loading_tips')});
	var params = {};
	params.page = p;
	$.post(WST.U('home/cashconfigs/pageQuery'),params,function(data,textStatus){
		layer.close(tips);
	    var json = WST.toJson(data);
	    if(json.status==1){
	    	json = json.data;
		    var gettpl = document.getElementById('config-list').innerHTML;
		    laytpl(gettpl).render(json.data, function(html){
		       	$('#config-page-list').html(html);
		    });
		    if(json.last_page>1){
		       	laypage({
			        cont: 'config-pager', 
			        pages:json.last_page, 
			        curr: json.current_page,
			        skin: '#e23e3d',
			        groups: 3,
			        jump: function(e, first){
			        	if(!first){
			        		pageConfigQuery(e.curr);
			        	}
			        } 
			    });
		     }else{
		       	 $('#config-pager').empty();
		     }
	    }
	});
}

function toEditConfig(id){
	var tips = WST.load({msg:WST.lang('loading_tips')});
	$.post(WST.U('home/cashconfigs/toEdit','id='+id),{},function(data,textStatus){
		layer.close(tips);
		w = WST.open({
		    type: 1,
		    title:((id>0)?WST.lang('edit'):WST.lang('add'))+WST.lang('drawal_account'),
		    shade: [0.6, '#000'],
		    border: [0],
		    content: data,
		    area: ['600px', '300px'],
		    offset: '100px'
		});
	});
} 
function editConfig(){
	$('#configForm').isValid(function(v){
		if(v){
			var params = WST.getParams('.j-ipt');
			params.accAreaId = WST.ITGetAreaVal('j-areas');
			var tips = WST.load({msg:WST.lang('submiting_tips')});
			$.post(WST.U('home/cashconfigs/'+((params.id>0)?'edit':'add')),params,function(data,textStatus){
				layer.close(tips);
			    var json = WST.toJson(data);
			    if(json.status==1){
		            WST.msg(json.msg,{icon:1},function(){
		            	pageConfigQuery(0);
		            	layer.closeAll();
		            });
			    }else{
			    	WST.msg(json.msg,{icon:2});
			    }
			});
		}
	});
}
function delConfig(id){
    WST.confirm({content:WST.lang('confirm_del_inquiry'),yes:function(){
   	    var tips = WST.load({msg:WST.lang('submiting_tips')});
	    $.post(WST.U('home/cashconfigs/del'),{id:id},function(data,textStatus){
		    layer.close(tips);
			var json = WST.toJson(data);
			if(json.status==1){
		        WST.msg(json.msg,{icon:1},function(){
		            pageConfigQuery(0);
		        });
			}else{
			    WST.msg(json.msg,{icon:2});
			}
	  });
   }})
}

function changeDrawMoney(obj){
	WST.isChinese(this,1);
	var commission = $('#commission').val();
	var totalMoney = $(obj).val()?$(obj).val():0;
	totalMoney = parseFloat(totalMoney);
	if(!totalMoney){
		$("#chargeService").html("0");
		$("#actualMoney").html("0");
		return;
	}
	var money = 0;
	if(commission!=undefined){
		money = (parseFloat(totalMoney)*parseFloat(commission)*0.01).toFixed(2);
	}
	$("#chargeService").html(money);
	$("#actualMoney").html((parseFloat(totalMoney-money)).toFixed(2));
}

function checkAccType(accType){
    if(accType<0){
    	$('.accType1').show();
    }else{
    	$('.accType1').hide();
    }
}