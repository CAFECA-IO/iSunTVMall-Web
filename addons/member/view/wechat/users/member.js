function qrCode(){
    $("#wst-dialog5").dialog("show");
}
function getAwardList(){
	  $('#Load').show();
	  loading = true;
	  var param = {};
	  param.pageSize = 10;
	  param.page = Number( $('#currPage').val() ) + 1;
	  param.type = $('#type').val();
	  $.post(WST.U('addon/member-member-querywechatmemberawards'), param,function(data){
	        var json = WST.toJson(data);
	        $('#currPage').val(json.current_page);
	        $('#totalPage').val(json.last_page);
	        var gettpl = document.getElementById('list').innerHTML;
	        laytpl(gettpl).render(json.data, function(html){
	            $('#data-list').append(html);
	        });
	        WST.imgAdapt('j-imgAdapt');
	        loading = false;
	        $('#Load').hide();
	        echo.init();//图片懒加载
	    });
}

function getusersList(){
	  $('#Load').show();
	  loading = true;
	  var param = {};
	  param.pageSize = 10;
	  param.page = Number( $('#currPage').val() ) + 1;
	  $.post(WST.U('addon/member-member-querywechatmemberusers'), param, function(data){
		  var json = WST.toJson(data);
	        $('#currPage').val(json.current_page);
	        $('#totalPage').val(json.last_page);
	        var gettpl = document.getElementById('list').innerHTML;
	        laytpl(gettpl).render(json.data, function(html){
	            $('#data-list').append(html);
	        });
	        WST.imgAdapt('j-imgAdapt');
	        loading = false;
	        $('#Load').hide();
	        echo.init();//图片懒加载
	  });
}
