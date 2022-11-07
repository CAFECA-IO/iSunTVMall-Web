var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('label_ads_posi_name'), name:'positionName', width: 100},
            {title:WST.lang('label_ads_posi_width'), name:'positionWidth' ,width:30},
            {title:WST.lang('label_ads_posi_height'), name:'positionHeight' ,width:30},
            {title:WST.lang('label_ads_posi_type'), name:'' ,width:30, align:'center',orders:10, renderer: function(val,item,rowIndex){
               var pName;
               switch(item['positionType']){
                  case 2:
                    pName=WST.lang('product_wechat');
                    break;
                  case 3:
                    pName=WST.lang('product_mobile');
                    break;
                  case 4:
                    pName=WST.lang('product_app');
                    break;
                  case 5:
                    pName=WST.lang('product_weapp');
                    break;
                  default:
                    pName=WST.lang('product_pc');
                    break;
               }
               return pName;
            }},
            {title:WST.lang('label_ads_posi_code'), name:'positionCode' ,width:40},
            {title:WST.lang('op'), name:'' ,width:120, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                if(WST.GRANT.GGGL_00)h += "<a  class='btn btn-blue' href='javascript:toAds("+item['positionId']+")'><i class='fa fa-pencil'></i>"+WST.lang('label_ads_posi_ads')+"</a> ";
                if(WST.GRANT.GGWZ_02)h += "<a  class='btn btn-blue' href='javascript:toEdit("+item['positionId']+")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if(WST.GRANT.GGWZ_03)h += "<a  class='btn btn-red' href='javascript:toDel(" + item['positionId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: h-184,indexCol: true, cols: cols,method:'POST',
        url: WST.U('admin/adpositions/pageQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         if(v){
             mmg.resize({height:h-185});
         }else{
             mmg.resize({height:h-137});
         }
    }});
    loadQuery(p);
}
function toEdit(id){
	location.href = WST.U('admin/adpositions/toedit','id='+id+'&p='+WST_CURR_PAGE);
}
function toAds(id){
	location.href = WST.U('admin/ads/index2','id='+id+'&p='+WST_CURR_PAGE);
}
function loadQuery(p){
    p=(p<=1)?1:p;
    var query = WST.getParams('.query');
    query.page = p;
    mmg.load(query);
}
function toDel(id){
	var box = WST.confirm({content:WST.lang('del_tips'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/adPositions/del'),{id:id},function(data,textStatus){
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



function editInit(p){
	 /* 表单验证 */
    $('#adPositionsForm').validator({
            fields: {
                positionType: {
                  rule:"required",
                  msg:{required:WST.lang('require_ads_posi_type')},
                  tip:WST.lang('require_ads_posi_type'),
                  ok:"",
                },
                positionName: {
                  rule:"required;",
                  msg:{required:WST.lang('require_ads_posi_name')},
                  tip:WST.lang('require_ads_posi_name'),
                  ok:"",
                },
                positionCode: {
                    rule:"required;",
                    msg:{required:WST.lang('require_ads_posi_code')},
                    tip:WST.lang('require_ads_posi_code'),
                    ok:"",
                  },
                positionWidth: {
                  rule:"required;",
                  msg:{required:WST.lang('require_ads_posi_width')},
                  ok:"",
                },
                positionHeight: {
                  rule:"required",
                  msg:{required:WST.lang('require_ads_posi_height')},
                  ok:"",
                }
            },
          valid: function(form){
            var params = WST.getParams('.ipt');
            var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
            $.post(WST.U('admin/adpositions/'+((params.positionId==0)?"add":"edit")),params,function(data,textStatus){
              layer.close(loading);
              var json = WST.toAdminJson(data);
              if(json.status=='1'){
                  WST.msg(json.msg,{icon:1});
                  location.href=WST.U('Admin/Adpositions/index',"p="+p);
              }else{
                    WST.msg(json.msg,{icon:2});
              }
            });
      }
    });
}
