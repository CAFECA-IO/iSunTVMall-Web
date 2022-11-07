var mmg;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('form_field'), name:'fieldName' ,width:100},
            {title:WST.lang('data_type'), name:'dataType' ,width:100},
            {title:WST.lang('form_title'), name:'fieldTitle' ,width:100},
            {title:WST.lang('data_length'), name:'dataLength' ,width:100},
            {ttitle:WST.lang('form_of_expression'), name:'fieldType' ,width:50},
            {title:WST.lang('shopFlows_tips12'), name:'fieldAttr' ,width:300,renderer: function(val,item,rowIndex){
                var h = '';
                var fieldType = item['fieldType'];
                switch(fieldType){
                    case 'input':
                        h = WST.lang('form_length')+"："+item['fieldAttr'];
                        break;
                    case 'textarea':
                        h = WST.lang('number_of_rows_and_columns')+"："+item['fieldAttr'];
                        break;
                    case 'radio':
                        h = WST.lang('radio_button_name')+"："+item['fieldAttr'];
                        break;
                    case 'checkbox':
                        h = WST.lang('multi_choice_button_name')+"："+item['fieldAttr'];
                        break;
                    case 'select':
                        h = WST.lang('drop_down_menu_value')+"："+item['fieldAttr'];
                        break;
                    case 'other':
                        h = "";
                        break;
                }
                return h;
            }},
            {title:WST.lang('form_notes'), name:'fieldComment' ,width:200},
            {title:WST.lang('sort'), name:'fieldSort' ,width:50},
            {title:WST.lang('is_it_required'), name:'isRequire' ,width:50,renderer: function(val,item,rowIndex){
                var h = '';
                item['isRequire'] == 0 ? h = "" : h = "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('yes')+"</span>";
                return h;
            }},
            {title:WST.lang('op'), name:'' ,width:150, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a  class='btn btn-blue' href='javascript:getForEdit(" + item['id'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
                if( item['isDelete'] == 1)h += "<a  class='btn btn-red' href='javascript:toDel(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: h-267,indexCol: true,indexColWidth:50, cols: cols,method:'POST',nowrap:true,
        url: WST.U('admin/shopflows/fieldPageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'',sortStatus:'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    $('#headTip').WSTTips({width:200,height:35,callback:function(v){
         if(v){
             mmg.resize({height:h-265});
         }else{
             mmg.resize({height:h-135});
         }
    }});
    loadGrid(p);
}
function loadGrid(p){
    var params = {};
    params.fId = $('#fId').val();
    params.fieldName = $('.fieldName').val();
    params.dataType = $(".dataType").val();
    params.fieldTitle = $('.fieldTitle').val();
    params.isRequire = $('.isRequire').val();
    params.fieldType = $('.fieldType').val();
    params.p=(p<=1)?1:p;
    mmg.load(params);
}

function getForEdit(id){
    if(id!=0){
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('admin/shopflows/getFieldById'),{id:id},function(data,textStatus){
            layer.close(loading);
            var json = WST.toAdminJson(data);
            if(json.id){
                WST.setValues(json);
                var text = '';
                var html = '';
                if(json.isDelete == 0){
                    $('#fieldName').attr('readonly',true).css('background','#eee');
                    $('#dataType').attr('disabled',true).css('background','#eee');
                    $('#dataLength').attr('readonly',true).css('background','#eee');
                    $('#isShow').attr('disabled',true).css('background','#eee');
                }else{
                    $('#fieldName').attr('readonly',false).css('background','#fff');
                    $('#dataType').attr('disabled',false).css('background','#fff');
                    $('#dataLength').attr('readonly',false).css('background','#fff');
                }
                // 当表单属性是系统预设的值，则不允许修改表单类型、表单属性、是否必填
                if(json.fieldAttr == 'custom') {
                    $('#fieldType').attr('disabled',true).css('background','#eee');
                    $('#isRequire').attr('disabled',true).css('background','#eee');
                    $('#fieldAttr').attr('readonly',true).css('background','#eee');
                }else{
                    $('#fieldType').attr('disabled',false).css('background','#fff');
                    $('#isRequire').attr('disabled',false).css('background','#fff');
                    $('#fieldAttr').attr('readonly',false).css('background','#fff');
                }
                switch(json.dataType) {
                    case 'date':
                        $('.dataLength').hide();
                        $('#dataLength').val(20);
                        break;
                    case 'time':
                        $('.dataLength').hide();
                        $('#dataLength').val(20);
                        break;
                    default:
                        $('.dataLength').show();
                        $('#dataLength').val(json.dataLength);
                        break;
                }
                $('.fieldRelevance').hide();
                $('.dateRelevance').hide();
                $('.timeRelevance').hide();
                $('.fileNum').hide();
                switch(json.fieldType){
                    case 'input':
                        text = WST.lang('form_length')+"<font color='red'>*</font>：";
                        break;
                    case 'textarea':
                        text = WST.lang('number_of_rows_and_columns')+"<font color='red'>*</font>：";
                        break;
                    case 'radio':
                        text = WST.lang('radio_button_name')+"<font color='red'>*</font>：";
                        //$('.fieldRelevance').show();
                        break;
                    case 'checkbox':
                        text = WST.lang('multi_choice_button_name')+"<font color='red'>*</font>：";
                        break;
                    case 'select':
                        text = WST.lang('drop_down_menu_value')+"<font color='red'>*</font>：";
                        break;
                    case 'other':
                        var areaCheck = '';
                        var dateCheck = '';
                        var timeCheck = '';
                        var fileCheck = '';
                        switch(json.fieldAttr) {
                            case 'area':
                                areaCheck = 'selected';
                                break;
                            case 'date':
                                dateCheck = 'selected';
                                $('.dateRelevance').show();
                                $('.isShow').show();
                                break;
                            case 'time':
                                timeCheck = 'selected';
                                $('.timeRelevance').show();
                                $('.isShow').show();
                                break;
                            case 'file':
                                fileCheck = 'selected';
                                $('.fileNum').show();
                                break;
                        }
                        text = WST.lang('goods_export_code_select')+"：";
                        html += "<select id='fieldAttr' name='fieldAttr' class='ipt' style='width:70%;padding-left:10px;' onchange='changeFieldAttrType(this)'>";
                        html += "<option value='area' "+areaCheck+">"+WST.lang('regional_type')+"</option>";
                        html += "<option value='date' "+dateCheck+">"+WST.lang('date_type')+"</option>";
                        html += "<option value='time' "+timeCheck+">"+WST.lang('time_type')+"</option>";
                        html += "<option value='file' "+fileCheck+">"+WST.lang('file_upload')+"</option>";
                        html += "</select>";
                        $('.fieldAttr').html(html);
                        break;
                }
                $('.fieldAttrTitle').html(text);
                toEdit(json.id);
            }else{
                WST.msg(json.msg,{icon:2});
            }
        });
    }else{
        $('#id').val('');
        $('#flowId').val($('#fId').val());
        $('#fieldName').attr('readonly',false);
        $('#dataType').attr('disabled',false);
        $('#dataLength').attr('readonly',false);
        $('.dateRelevance').hide();
        $('.timeRelevance').hide();
        $('.fileNum').hide();
        //$('.fieldRelevance').hide();
        toEdit(0);
    }
}

function toEdit(id){
    var text = "<span>"+WST.lang('form_length')+"<font color='red'>*</font>：<span>";
    var html = "<input type='text'  id='fieldAttr' name='fieldAttr'  style='width:70%;' class='ipt' value='' data-rule='"+WST.lang('shopFlows_tips12')+":required;'  data-msg-required='"+WST.lang('shopFlows_tips13')+"' data-target='#fieldAttrMsg'/><span id='fieldAttrMsg'></span>";
    var title =(id==0)?WST.lang('add'):WST.lang('edit');
    var obj = $('#fieldBox');
    var box =WST.open({title:title,type:1,offset:'0px',content:obj,area: [WST.pageWidth()+'px',WST.pageHeight()+'px'],btn: [WST.lang('confirm'),WST.lang('cancel')],yes:function(){
        $('#fieldForm').isValid(function(v) {
            if (v) {
                var params = WST.getParams('.ipt');
                var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
                $.post(WST.U('admin/shopflows/saveField'),params,function(data,textStatus){
                    layer.close(loading);
                    var json = WST.toAdminJson(data);
                    if(json.status=='1'){
                        WST.msg(json.msg,{icon:1});
                        $('#fieldForm')[0].reset();
                        $('.fieldAttr').html(html);
                        $('.fieldAttrTitle').html(text);
                        layer.close(box);
                        loadGrid(WST_CURR_PAGE);
                    }else{
                        WST.msg(json.msg,{icon:2});
                    }
                });
            }
        });
    },cancel:function(){
        //重置表单
        $('#fieldForm')[0].reset();
        $('.fieldAttr').html(html);
        $('.fieldAttrTitle').html(text);
    },end:function(){
        $('#fieldBox').hide();
        //重置表单
        $('#fieldForm')[0].reset();
        $('.fieldAttr').html(html);
        $('.fieldAttrTitle').html(text);
    }});
}

function changeDataType(obj) {
    var dataType = $(obj).val();
    switch(dataType) {
        case 'date':
            $('.dataLength').hide();
            $('#dataLength').val(20);
            break;
        case 'time':
            $('.dataLength').hide();
            $('#dataLength').val(20);
            break;
        default:
            $('.dataLength').show();
            $('#dataLength').val('');
            break;
    }
}

function changeFieldAttrType(obj) {
    var fieldAttrType = $(obj).val();
    switch(fieldAttrType) {
        case 'date':
            $('.dateRelevance').show();
            $('.timeRelevance').hide();
            $('.fileNum').hide();
            break;
        case 'time':
            $('.timeRelevance').show();
            $('.dateRelevance').hide();
            $('.fileNum').hide();
            break;
        case 'file':
            $('.fileNum').show();
            $('.dateRelevance').hide();
            $('.timeRelevance').hide();
            break;
        default:
            $('.dateRelevance').hide();
            $('.timeRelevance').hide();
            $('.fileNum').hide();
            break;
    }
}

function changeFieldType(obj){
    var fieldType = $(obj).val();
    $('.fieldAttr').html('');
    //$('.fieldRelevance').hide();
    $('.dateRelevance').hide();
    $('.timeRelevance').hide();
    $('.fileNum').hide();
    var html = '';
    var text = '';
    switch(fieldType){
        case 'input':
            text = "<span>"+WST.lang('form_length')+"<font color='red'>*</font>：<span>";
            html = "<input type='text'  id='fieldAttr' name='fieldAttr'  style='width:70%;' class='ipt' value='' data-rule='"+WST.lang('shopFlows_tips12')+":required;'  data-msg-required='"+WST.lang('shopFlows_tips13')+"' data-target='#fieldAttrMsg'/><span id='fieldAttrMsg'></span>";
            break;
        case 'textarea':
            text = "<span>"+WST.lang('number_of_rows_and_columns')+"<font color='red'>*</font>：<span>";
            html = "<input type='text'  id='fieldAttr' name='fieldAttr' placeholder='"+WST.lang('shopFlows_tips20')+"' style='width:70%;' class='ipt' value='' data-rule='"+WST.lang('shopFlows_tips12')+":required;'  data-msg-required='"+WST.lang('shopFlows_tips13')+"' data-target='#fieldAttrMsg'/><span id='fieldAttrMsg'></span>";
            break;
        case 'radio':
            text = "<span>"+WST.lang('radio_button_name')+"<font color='red'>*</font>：<span>";
            html = "<input type='text'  id='fieldAttr' name='fieldAttr' placeholder='"+WST.lang('shopFlows_tips21')+"' style='width:70%;' class='ipt' value='' data-rule='"+WST.lang('shopFlows_tips12')+":required;'  data-msg-required='"+WST.lang('shopFlows_tips13')+"' data-target='#fieldAttrMsg'/><span id='fieldAttrMsg'></span>";
            //$('.fieldRelevance').show();
            break;
        case 'checkbox':
            text = "<span>"+WST.lang('multi_choice_button_name')+"<font color='red'>*</font>：<span>";
            html = "<input type='text'  id='fieldAttr' name='fieldAttr' placeholder='"+WST.lang('shopFlows_tips21')+"' style='width:70%;' class='ipt' value='' data-rule='"+WST.lang('shopFlows_tips12')+":required;'  data-msg-required='"+WST.lang('shopFlows_tips13')+"' data-target='#fieldAttrMsg'/><span id='fieldAttrMsg'></span>";
            break;
        case 'select':
            text = "<span>"+WST.lang('drop_down_menu_value')+"<font color='red'>*</font>：<span>";
            html = "<input type='text'  id='fieldAttr' name='fieldAttr' placeholder='"+WST.lang('shopFlows_tips21')+"' style='width:70%;' class='ipt' value='' data-rule='"+WST.lang('shopFlows_tips12')+":required;'  data-msg-required='"+WST.lang('shopFlows_tips13')+"' data-target='#fieldAttrMsg'/><span id='fieldAttrMsg'></span>";
            break;
        case 'other':
            text = "<span>"+WST.lang('goods_export_code_select')+"&nbsp;：<span>";
            html += "<select id='fieldAttr' name='fieldAttr' class='ipt' style='width:70%;padding-left:10px;' onchange='changeFieldAttrType(this)'>";
            html += "<option value='area' >"+WST.lang('regional_type')+"</option>";
            html += "<option value='date' >"+WST.lang('date_type')+"</option>";
            html += "<option value='time' >"+WST.lang('time_type')+"</option>";
            html += "<option value='file' >"+WST.lang('file_upload')+"</option>";
            html += "</select>";
            break;
    }
    $('.fieldAttr').html(html);
    $('.fieldAttrTitle').html(text);
}

function toDel(id){
    var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_delete_it'),yes:function(){
        var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
        $.post(WST.U('admin/shopflows/delField'),{id:id},function(data,textStatus){
            layer.close(loading);
            var json = WST.toAdminJson(data);
            if(json.status=='1'){
                WST.msg(WST.lang('op_ok'),{icon:1});
                layer.close(box);
                loadGrid(WST_CURR_PAGE)
            }else{
                WST.msg(json.msg,{icon:2});
            }
        });
    }});
}
