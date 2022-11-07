var mmg;
function initGrid(p){
  var positionType = $("#positionType").val();
  var adPositionId = $("#adPositionId").val();
  var h = WST.pageHeight();
  var cols = [
            {title:WST.lang('upload_img2'), name:'' ,width:50, align:'center', renderer: function(val,item,rowIndex){
               var adFile = item['adFile'].split(',');
               return'<img src="'+WST.conf.RESOURCE_PATH+'/'+adFile[0]+'" style="max-width:100px; max-height:80px;"/>';
            }},
            {title:WST.lang('label_ads_name'), name:'adName', width: 100},
            {title:WST.lang('label_ads_posi'), name:'positionName' ,width:80},
            {title:WST.lang('label_ads_url'), name:'adURL' ,width:130},
            {title:WST.lang('label_ads_start'), name:'adStartDate' ,width:30},
            {title:WST.lang('label_ads_end'), name:'adEndDate' ,width:30},
            
            {title:WST.lang('label_ads_click_num'), name:'adClickNum' ,width:15},
            {title:WST.lang('sort'), name:'adSort' ,width:15, renderer: function(val,item,rowIndex){
               return '<span style="color:blue;cursor:pointer;" ondblclick="changeSort(this,'+item["adId"]+');">'+val+'</span>';
            }},
            {title:WST.lang('op'), name:'' ,width:80, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(adPositionId>0){
                	if(WST.GRANT.GGGL_02)h += "<a  class='btn btn-blue' href='javascript:toEdit2("+item['adId']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                }else{
                	if(WST.GRANT.GGGL_02)h += "<a  class='btn btn-blue' href='javascript:toEdit("+item['adId']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                }
                if(WST.GRANT.GGGL_03)h += "<a  class='btn btn-red' href='javascript:toDel(" + item['adId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];
 
    mmg = $('.mmg').mmGrid({height: h-170,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/ads/pageQuery','positionType='+positionType+'&adPositionId='+adPositionId), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });  
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         if(v){
             mmg.resize({height:h-170});
         }else{
             mmg.resize({height:h-139});
         }
    }});
    loadQuery(p);
}
function toEdit(id){
    location.href = WST.U('admin/ads/toedit','id='+id+'&p='+WST_CURR_PAGE);
}
function toEdit2(id){
	var adPositionId = $("#adPositionId").val();
    location.href = WST.U('admin/ads/toedit2','id='+id+'&adPositionId='+adPositionId+'&p='+WST_CURR_PAGE);
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/ads/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(json.msg,{icon:1});
	           			    	layer.close(box);
	           		        loadQuery(WST_CURR_PAGE);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}
function loadQuery(p){
    p=(p<=1)?1:p;
    var query = WST.getParams('.query');
    query.page = p;
    mmg.load(query);
}
var oldSort;
function changeSort(t,id){
   $(t).attr('ondblclick'," ");
var html = "<input type='text' id='sort-"+id+"' style='width:30px;padding:2px;' onblur='doneChange(this,"+id+")' value='"+$(t).html()+"' />";
 $(t).html(html);
 $('#sort-'+id).focus();
 $('#sort-'+id).select();
 oldSort = $(t).html();
}
function doneChange(t,id){
  var sort = ($(t).val()=='')?0:$(t).val();
  if(sort==oldSort){
    $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
    $(t).parent().html(parseInt(sort));
    return;
  }
  $.post(WST.U('admin/ads/changeSort'),{id:id,adSort:sort},function(data){
    var json = WST.toAdminJson(data);
    if(json.status==1){
        $(t).parent().attr('ondblclick','changeSort(this,'+id+')');
        $(t).parent().html(parseInt(sort));
    }
  });
}


		
//查询
function adsQuery(){
		var query = WST.getParams('.query');
	    grid.set('url',WST.U('admin/ads/pageQuery',query));
}

var isContinueAdd = false;
function save(){
   isContinueAdd = false;
   $('#adsForm').submit();
}
function continueAdd(){
   isContinueAdd = true;
   $('#adsForm').submit();
}
function editInit(p){
  var laydate = layui.laydate;
    form = layui.form; 
    laydate.render({elem: '#adStartDate'});
    laydate.render({elem: '#adEndDate'});
  //文件上传
	WST.upload({
  	  pick:'#adFilePicker',
  	  formData: {dir:'adspic'},
      compress:false,//默认不对图片进行压缩
  	  accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
  	  callback:function(f){
  		  var json = WST.toAdminJson(f);
  		  if(json.status==1){
  			$('#uploadMsg').empty().hide();
        var html = '<img src="'+WST.conf.RESOURCE_PATH+'/'+json.savePath+json.thumb+'" />';
        $('#preview').html(html);
        // 图片路径
        $('#adFile').val(json.savePath+json.thumb);
  		  }
	  },
	  progress:function(rate){
	      $('#uploadMsg').show().html(WST.lang('upload_rate')+rate+"%");
	  }
    });
  
 /* 表单验证 */
    $('#adsForm').validator({
    		timely:2,
            fields: {
                adPositionId: {
                  rule:"required",
                  msg:{required:WST.lang('require_ads_posi')},
                  tip:WST.lang('require_ads_posi'),
                  ok:WST.lang('require_ads_check_ok')
                },
                adName: {
                  rule:"required;",
                  msg:{required:WST.lang('require_ads_name')},
                  tip:WST.lang('require_ads_name'),
                  ok:WST.lang('require_ads_check_ok')
                },
                adFile: {
                  rule:"required;",
                  msg:{required:WST.lang('require_upload_img')},
                  tip:WST.lang('require_upload_img'),
                  ok:"",
                },
                adStartDate: {
                  rule:"required;match(lt, adEndDate, date)",
                  msg:{required:WST.lang('require_ads_start'),match:WST.lang('require_ads_start2')},
                  ok:WST.lang('require_ads_check_ok')
                },
                adEndDate: {
                  rule:"required;match(gt, adStartDate, date)",
                  msg:{required:WST.lang('require_ads_end'),match:WST.lang('require_ads_end2')},
                  ok:WST.lang('require_ads_check_ok')
                }
            },
          valid: function(form){
            var params = WST.getParams('.ipt');
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/Ads/'+((params.adId==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toAdminJson(data);
              if(json.status=='1'){
                  WST.msg(json.msg,{icon:1});
                  if(isContinueAdd){
                     $('#adsForm').get(0).reset();
                     $('#preview').empty();
                     $('#adFile').val('');
                  }else{
                	  var positionId = $("#positionId").val();
                	  if(positionId>0){
                		  location.href = WST.U('admin/ads/index2','id='+positionId+'&p='+p);
                	  }else{
                		  location.href = WST.U('Admin/Ads/index',"p="+p);
                	  }
                  }
              }else{
                    WST.msg(json.msg,{icon:2});
              }
            });
      }
    });

    // app页面说明
    $.post(WST.U('admin/appscreens/pagequery'),{},function(responData){
      appScreens = responData;
    });
}

// app按钮
var appScreens = [];

var positionInfo;
/*获取地址*/
function addPosition(pType, val, getSize)
{
    $.post(WST.U('admin/Adpositions/getPositon'),{'positionType':pType},function(data,textStatus){
        positionInfo = data;
        var html='<option value="">'+WST.lang('select')+'</option>';
        $(data).each(function(k,v){
			var selected;
            if(v.positionId==val){
              selected = 'selected="selected"';
              getPhotoSize(v.positionId);
            }
            html +='<option '+selected+' value="'+v.positionId+'">'+v.positionName+'</option>';
        });
        $('#adPositionId').html(html);
        layui.form.render('select');

        // app内页面跳转说明
        if(pType==4){// App端
           if($('#appBtns').length==0){
               var options = ['<option value="0">'+WST.lang('require_ads_page_tips')+'</option>'];
               for(var i in appScreens){
                  var _obj = appScreens[i];
                  options.push('<option value="'+_obj.explain+'">'+_obj.screenName+'</option>');
               }
               var select = '<select id="appBtns" >'+options.join('')+'</select>'
               var html= '<tr><th>'+WST.lang('require_ads_page_tips2')+'：</th><td>'+select+'</td></tr><tr id="screenExplain"><th></th><td></td></tr>';
               $('#adsBtnType').after(html);
               $('#appBtns').change(function(v){
                  var _explain = $(this).val()==0?'':'<span style="color:red">'+WST.lang('require_ads_page_tips3')+'：</span>'+$(this).val();
                  $('#screenExplain td').html(_explain);
               })
           }
        }else{
          $('#appBtns').parent().parent().remove();
          $('#screenExplain').remove();
        }




    })
}




/*获取图片尺寸 以及设置图片显示方式*/
function getPhotoSize(pType)
{
  $(positionInfo).each(function(k,v){
      if(v.positionId==pType){
        $('#img_size').html(v.positionWidth+'x'+v.positionHeight);
        if(v.positionWidth>v.positionHeight){
             $('.ads-h-list').removeClass('ads-h-list').addClass('ads-w-list');
         }
      }
  });

}