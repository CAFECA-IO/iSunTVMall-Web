function userAddrEditInit(){
 /* 表单验证 */
    $('#useraddressForm').validator({
          fields: {
                userAddress: {
                  rule:"required;length[~60, true]",
                  msg:{required:WST.lang('require_detail_address')},
                  tip:WST.lang('require_detail_address'),
                  ok:"",
                },
                userName: {
                  rule:"required;length[~12, true]",
                  msg:{required:WST.lang('require_contact_name')},
                  tip:WST.lang('require_contact_name'),
                  ok:"",
                },
                userPhone: {
                  rule:"required;length[~50, true]",
                  msg:{required:WST.lang('require_contact_number')},
                  tip:WST.lang('require_contact_number'),
                  ok:"",
                },
                isDefault: {
                    rule:"checked;",
                    msg:{checked:WST.lang('is_default_address')},
                    tip:WST.lang('is_default_address'),
                    ok:"",
                }
          },
          valid: function(form){
        	var isNoSelected = false;
        	$('.j-areas').each(function(){
        		isSelected = true;
        		if($(this).val()==''){
        		    isNoSelected = true;
        			return;
        		}
        	});
        	if(isNoSelected){
        		WST.msg(WST.lang('require_all_area'),{icon:2});
        		return;
        	}
            var params = WST.getParams('.ipt');
            params.areaId = WST.ITGetAreaVal('j-areas');
            var loading = WST.msg(WST.lang('loading_tips'), {icon: 16,time:60000});
            $.post(WST.U('home/useraddress/'+((params.addressId==0)?"add":"toEdit")),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toJson(data);
              if(json.status=='1'){
                  WST.msg(json.msg,{icon:1});
                  location.href=WST.U('home/useraddress/index');
              }else{
                    WST.msg(json.msg,{icon:2});
              }
            });

      }

    });
 }
function listQuery(){
   $.post(WST.U('Home/Useraddress/listQuery'),'',function(data,textStatus){
    var json = WST.toJson(data);
    if(json.status==1 && json.data){
      json = json.data;
	    var count = json.length;//已添加的记录数
	    $('.g1').html(count);
	    var gettpl = document.getElementById('address').innerHTML;
	    laytpl(gettpl).render(json, function(html){
	        $('#address_box').html(html);
	    });
    }else{
    	$('#address_box').empty();
    }
});
}

function editAddress(id){
   location.href=WST.U('home/useraddress/edit','id='+id);
}

function delAddress(id,t){
  WST.confirm({content:WST.lang('confirm_del_inquiry'),yes:function(tips){
    var ll = layer.load(WST.lang('submiting_tips'));
    $.post(WST.U('Home/UserAddress/del'),{id:id},function(data,textStatus){
      layer.close(ll);
        layer.close(tips);
      var json = WST.toJson(data);
      if(json.status=='1'){
        WST.msg(WST.lang('operation_success'), {icon: 1}, function(){
        	listQuery();
        });
      }else{
        WST.msg(WST.lang('operation_fail'), {icon: 5});
      }
    });
  }});

}
function setDefault(id){
   WST.confirm({content:WST.lang('confirm_set_default_address'),yes:function(tips){
    var ll = layer.load(WST.lang('submiting_tips'));
    $.post(WST.U('Home/UserAddress/setDefault'),{id:id},function(data,textStatus){
      layer.close(ll);
        layer.close(tips);
      var json = WST.toJson(data);
      if(json.status=='1'){
        WST.msg(WST.lang('operation_success'), {icon: 1}, function(){
        	listQuery();
        });
      }else{
        WST.msg(WST.lang('operation_fail'), {icon: 5});
      }
    });
  }});
}
