var WST = WST || {};
WST.toJson = function(str){
	var json = {};
	try{
		if(typeof(str )=="object"){
			json = str;
		}else{
			json = eval("("+str+")");
		}
		if(json.status && json.status=='-999'){
			alert('对不起，您已经退出系统！请重新登录');
			if(window.parent){
				window.parent.location.reload();
			}else{
				location.reload();
			}
		}else if(json.status && json.status=='-998'){
			alert('对不起，您没有操作权限，请与管理员联系');
			return;
		}
	}catch(e){
		//alert("系统发生错误:"+e.getMessage,{icon:5});
		json = {};
	}
	return json;
}
$(document).ready(function(){
	if($('.check-1')[0])$('.nextBtn').attr('disabled',true);
    $('#admin_install').click(function(){
        var admin_name = $('#admin_name').val();
        var admin_password = $('#admin_password').val();
        var admin_password2 = $('#admin_password2').val();
        var dbHost = $('#dbHost').val();
        var dbPort = $('#dbPort').val();
        var dbUser = $('#dbUser').val();
        var dbPass = $('#').val();
        var dbPrefix = $('#dbPrefix').val();
        var dbName = $('#dbName').val();
        var curStep = Number( $('#curStep').val() );
            
        data = {
            action: 'admin_info',
            dbPort: dbPort,
            dbHost: dbHost,
            dbUser: dbUser,
            dbPass: dbPass,
            dbPrefix: dbPrefix,
            dbName: dbName,
            admin_name: admin_name,
            admin_password: admin_password
        };
        url = 'ajax.php';
        $(this).html('loading...');
        $.post(url,data,function(status){
        });
    });
});
function checkVal(name){
   if( $('#'+name).val() == ''){
        $('.'+name).show().addClass('red');
   }else{
    	$('.'+name).hide().removeClass('red');
   }
}
var dataConfig = {};
var dataTables = [];
function initDataBase(){
	var check = true;
	dataConfig.db_host = $('#db_host').val();
    dataConfig.db_port = $('#db_port').val();
	dataConfig.db_user = $('#db_user').val();
	dataConfig.db_pass = $('#db_pass').val();
	dataConfig.db_prefix = $('#db_prefix').val();
    dataConfig.db_charset = $('#db_charset').val();
	dataConfig.db_name = $('#db_name').val();
	dataConfig.admin_name = $('#admin_name').val();
	dataConfig.admin_password = $('#admin_password').val();
	dataConfig.admin_password2 = $('#admin_password2').val();
    if( dataConfig.db_host == ''){
        $('.db_host').show().addClass('red');
        check = false;
    }
    if( dataConfig.db_port == ''){
        $('.db_port').show().addClass('red');
        check = false;
    }
    if( dataConfig.db_user == ''){
        $('.db_user').show().addClass('red');
        check = false;
    }
    if( dataConfig.db_name == ''){
    	$('.db_name').html('数据库名不能为空');
        $('.db_name').show().addClass('red');
        check = false;
    }
    var reg = /^[a-zA-Z0-9_]{1,16}$/;
    if(!reg.test(dataConfig.db_name)){
    	$('.db_name').html('数据库名在1到16位之间数字或字母以及下划线，不能含有特殊字符');
    	$('.db_name').show().addClass('red');
    	check = false;
    }
    if( dataConfig.admin_name == ''){
        $('.admin_name').show().addClass('red');
        check = false;
    }
    if( dataConfig.admin_password == ''){
        $('.admin_password').show().addClass('red');
        check = false;
    }
    if( dataConfig.admin_password2 == ''){
        $('.admin_password2').html('请再次输入密码').show().addClass('red');
        check = false;
    }
    if(dataConfig.admin_password != dataConfig.admin_password2 ){
        $('.admin_password2').html('两次输入的密码不正确').show().addClass('red');
        check = false;
    }
    dataConfig.db_demo = document.getElementById('db_demo').checked?1:0;
    if(!check)return;
	getTableList();
}
function getTableList(){
	$('.btn').hide();
	$('#init_msg').show();
   	$('.nav ul li').eq(2).removeClass("li_action");
    $('.nav ul li').eq(3).addClass("li_action");
	dataConfig.act = 'list';
	dataConfig.isFinish = 0;
	dataTables.length = 0;
	var url = 'include/install_api.php';
    $('#init_msg').show();
    $('.btn').hide();
    $('#data_init').empty();
    $.post(url,dataConfig,function(status){
    	var json = WST.toJson(status);
    	var table = null;
        if(json.status==1){
        	for(var i=0;i<json.list.length;i++){
        		table = json.list[i].replace('wst_','').replace('.sql','');
        		dataTables.push(table);
        		$('<div><img src="../install/images/database.png" id="data_'+table+'"></span>&nbsp;'+table+'</div>').appendTo('#data_init');
        	}
        	$('#data_config').slideUp();
        	$('#data_init').slideDown(300,function(){
        	    recursionTable(0);
        	});
        }else{
        	$('#init_msg').hide();
            $('.btn').show();
            $('#data_config').slideDown();
            $('#data_init').slideUp();
            alert('安装数据库出错：'+json.msg);
        }
    });
}
function recursionTable(key){
	dataConfig.act = 'insert';
	if(key==(dataTables.length-1))dataConfig.isFinish = 1;
	dataConfig.table = dataTables[key]
	var url = 'include/install_api.php';
	$('#data_'+dataConfig.table).attr('src','../install/images/loading-2.gif');
	$.post(url,dataConfig,function(status){
		var json = WST.toJson(status);
		if(json.status==1){
			if(key<(dataTables.length-1)){
				$('#data_'+dataConfig.table).attr('src','../install/images/ok.gif');
				key++
				recursionTable(key);
			}else{
				location.href='index.php?step=3';
			}
		}else{
			$('#init_msg').hide();
            $('.btn').show();
			$('#'+dataConfig.table).attr('src','../install/images/unkown.gif');
			dataTables = [];
			alert(json.msg);
		}
	});
}
function showStep(step){
	if(step==1){
		$('#step').val(1);
		$('#rnd').val(Math.random());
		$('#form1').submit();
	}else if(step==2){
		$('#step').val(2);
		$('#form1').submit();

	}else if(step==3){
		$('#step').val(3);
		initDataBase();
	}else if(step==4){
		$('#step').val(4);
		$('#form1').submit();
	}else if(step==5){
		$('#step').val(5);
		$('#form1').submit();
	}
}

function checkRoute(){
    $.ajax({type: "POST",url: '../home/index/checkroute',async: true,timeout: 10000,
       success: function(msg){
           var json = WST.toJson(status);
           if(json.status){
               $('#checkRoute').html("您的系统暂不支持URL重写，请参考：<a target='_blank' href='https://www.kancloud.cn/manual/thinkphp5_1/353955'>URL重写设置</a>").css('color','red');
           }
       },
       error: function(msg){
           $('#checkRoute').html("您的系统暂不支持URL重写，请参考：<a target='_blank' href='https://www.kancloud.cn/manual/thinkphp5_1/353955'>URL重写设置</a>").css('color','red');
      }
    });
}