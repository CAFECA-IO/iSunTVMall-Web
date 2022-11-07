$(function () {
	$('#tab').TabPanel({tab:0,callback:function(tab){
		switch(tab){
		   case 0:pageQuery();break;
		   case 1:pageConfigQuery(0);break;
		}	
	}})
});
var isSetPayPwd = 1;
function getSupplierMoney(){
	$.post(WST.U('supplier/suppliers/getSupplierMoney'),{},function(data,textStatus){
		var json = WST.toJson(data);
		if(json.status==1){
			var supplierMoney = json.data.supplierMoney;
			var rechargeMoney = json.data.rechargeMoney;
			$('#supplierMoney').html(WST.lang('currency_symbol')+supplierMoney);
			$('#lockMoney').html(WST.lang('currency_symbol')+json.data.lockMoney);
			rechargeMoney = parseFloat(supplierMoney - rechargeMoney)
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
function pageQuery(){
    var h = WST.pageHeight();
    var cols = [
        {title:WST.lang('withdrawal_order_No'), name:'cashNo', width: 100},
        {title:WST.lang('withdrawal_account_information'), name:'accTargetName', width: 100,renderer:function(val,item,rowIndex){
        	if(item['accType']==1){
                return item['accNo'];
        	}else{
        	    return item['accTargetName']+'|'+item['accNo'];
            }
        }},
        {title:WST.lang('cashdraws_msgs2'), name:'accUser', width: 100},
        {title:WST.lang('withdrawal_amount'), name:'money', width: 50,renderer:function(val,item,rowIndex){return WST.lang('currency_symbol')+val;}},
        {title:WST.lang('label_settlement_create_time'), name:'createTime', width: 100},
        {title:WST.lang('withdrawal_status'), name:'', width: 250,renderer:function(val,item,rowIndex){
                if(item['cashSatus']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('successful_withdrawal')+"</span>";
                }else if(item['cashSatus']==-1){
                    return "<span class='statu-no'>"+WST.lang('withdrawal_failure')+"<br/>"+WST.lang('cashdraws_msg2')+item['cashRemarks']+"</span>";
                }else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('complaint_status1')+"</span>";
				}
            }},
    ];

    mmg = $('.mmg').mmGrid({height: h-193,indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.U('supplier/cashdraws/pageQueryBySupplier'), fullWidthRows: true, autoLoad: true,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
}
function loadGrid(){
    mmg.load({page:1});
}
var w;
function toDrawMoney(){
	if(isSetPayPwd==0){
		WST.msg(WST.lang('cashdraws_msgs1'),{icon:2,time:1000},function(){
			location.href = WST.U('supplier/users/security');
		});
		return;
	}
    var tips = WST.load({msg:WST.lang('loading')});
	$.post(WST.U('supplier/cashdraws/toEditBySupplier'),{},function(data,textStatus){
		layer.close(tips);
		w = WST.open({
		    type: 1,
		    title:WST.lang('application_for_withdrawal'),
		    shade: [0.6, '#000'],
		    border: [0],
		    content: data,
		    area: ['550px', '450px'],
		    offset: '100px'
		});
	});
}
function drawMoney(){
	$('#drawForm').isValid(function(v){
		if(v){
			var params = WST.getParams('.j-ipt');
			if(params.accType==1 && $.trim(params.accUser)==''){
                WST.msg(WST.lang('please_enter_your_real_name'),{icon:2,time:3000});
                return;
			}
		    if(window.conf.IS_CRYPT=='1'){
		        var public_key=$('#token').val();
		        var exponent="10001";
		   	    var rsa = new RSAKey();
		        rsa.setPublic(public_key, exponent);
		        params.payPwd = rsa.encrypt(params.payPwd);
		    }
			var tips = WST.load({msg:WST.lang('loading')});
			$.post(WST.U('supplier/cashdraws/drawMoneyBySupplier'),params,function(data,textStatus){
				layer.close(tips);
			    var json = WST.toJson(data);
			    if(json.status==1){
		            WST.msg(json.msg,{icon:1},function(){
                        loadGrid();
		            	getSupplierMoney();
		            	layer.close(w);
		            });
			    }else{
			    	WST.msg(json.msg,{icon:2});
			    }
			});
		}
	});
}
function layerclose(){
  layer.close(w);
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
function changeAccType(v){
	$('.accType').hide();
    $('.accType'+v).show()
}