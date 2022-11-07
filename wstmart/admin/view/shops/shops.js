var mmg,mmg2;
function initGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('shop_number'), name:'shopSn', width: 30,sortable: true},
            {title:WST.lang('store_account_number'), name:'loginName',width: 60,sortable: true},
            {title:WST.lang('brand_apply_shop_name'), name:'shopName',width: 80,sortable: true,renderer: function (val,item,rowIndex){
					return "<a href=\"javascript:toView(" + item['shopId'] + ",\'index\')\">"+item['shopName']+"</a>";
				}},
            {title:WST.lang('industry'), name:'tradeName',width: 80,sortable: true},
            {title:WST.lang('owner_name'), name:'shopkeeper',width: 40,hidden: true,sortable: true},
            {title:WST.lang('shopkeeper_contact_number'), name:'telephone',width: 30,hidden: true,sortable: true},
            {title:WST.lang('shop_address'), name:'shopAddress',width:200 },
            {title:WST.lang('company'), name:'shopCompany',width: 60,hidden: true},
            {title:WST.lang('business_status'), name:'shopAtive' ,width: 20,sortable: true,renderer: function (val,item,rowIndex){
	        	return (item['shopAtive']==1)?"<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('in_business')+"</span>":"<span class='statu-wait'><i class='fa fa-coffee'></i> "+WST.lang('resting')+"</span>";
	        }},
			{title:WST.lang('due_date'), name:'expireDate' ,width: 20,sortable: true,renderer: function (val,item,rowIndex){
				return (item['isExpire']==true)?"<span class='expire-yes'>"+item['expireDate']+"</span>":"<span>"+item['expireDate']+"</span>";
			}},
            {title:WST.lang('op'), name:'' ,width:150, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(WST.GRANT.DPGL_02)h += "<a class='btn btn-blue' href=\"javascript:toEdit(" + item['shopId'] + ",\'index\')\"><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
	            if(WST.GRANT.DPGL_03 && item['shopId']!=1)h += "<a class='btn btn-red' href='javascript:toDel(" + item['shopId'] + ",1)'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
	            h += "<a class='btn btn-blue' href='"+WST.U('admin/logmoneys/tologmoneys','id='+item['shopId']+'&src=shops&p='+WST_CURR_PAGE)+"&type=1'><i class='fa fa-search'></i>"+WST.lang('label_finance_shop_money1')+"</a>";
	            return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: (h-90),indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.U('admin/shops/pageQuery'), fullWidthRows: true, autoLoad: false,
        remoteSort:true ,
        sortName: 'shopSn',
        sortStatus: 'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid(p);
}
function loadGrid(p){
	p=(p<=1)?1:p;
	var params = WST.getParams('.j-ipt');
	params.areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
	params.page = p;
	mmg.load(params);
}
function initApplyGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('applicant_account_number'), name:'loginName', width: 50},
			{title:WST.lang('brand_apply_shop_name'), name:'shopName',width: 80,sortable: true,renderer: function (val,item,rowIndex){
					return "<a href=\"javascript:toView(" + item['shopId'] + ",\'apply\')\">"+item['shopName']+"</a>";
				}},
            {title:WST.lang('industry'), name:'tradeName',width: 80,sortable: true},
            {title:WST.lang('company'), name:'shopCompany',width:80 },
            {title:WST.lang('application_contact'), name:'applyLinkMan',width:30 },
            {title:WST.lang('telephone_number_of_application_contact'), name:'applyLinkTel',width:60,renderer: function (val,item,rowIndex){
	        	if(val)return "+"+item['applyLinkTelAreaCode']+" "+val;
	        } },
            {title:WST.lang('docking_with_merchants_of_shopping_mall'), name:'isInvestment' ,width:60,renderer: function (val,item,rowIndex){
	        	return (item['isInvestment']==1)?item['investmentStaff']:'-';
	        }},
            {title:WST.lang('date_of_application'), name:'applyTime' },
            {title:WST.lang('application_status'), name:'applyStatus' ,width:30,renderer: function (val,item,rowIndex){
	        	if(item['applyStatus']==1){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('label_cashdraws_status2')+"</span>";
	        	}else if(item['applyStatus']==0){
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('filling_in')+"</span>";
	        	}else{
                    return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('application_failed')+"</span>";
	        	}
	        }},
            {title:WST.lang('op'), name:'' ,width:80, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            if(WST.GRANT.DPSQ_04)h += "<a class='btn btn-blue' href='javascript:toHandle(" + item['shopId'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('op')+"</a> ";
	            if(WST.GRANT.DPSQ_03)h += "<a class='btn btn-red' href='javascript:toDelApply(" + item['shopId'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
	            return h;
            }}
            ];

    mmg = $('#mmg').mmGrid({height: (h-135),indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.U('admin/shops/pageQueryByApply'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadApplyGrid(p);
}
function loadApplyGrid(p){
	p=(p<=1)?1:p;
	var params = WST.getParams('.j-ipt');
	params.areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
	params.page = p;
	mmg.load(params);
}
function toHandle(id){
	location.href = WST.U('admin/shops/toHandleApply','id='+id+'&p='+WST_CURR_PAGE);
}
function toDelApply(id){
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_delete_it'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           $.post(WST.U('admin/shops/delApply'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('op_ok'),{icon:1});
	           			    	layer.close(box);
	           		            loadApplyGrid(WST_CURR_PAGE);
	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}
function initStopGrid(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('shop_number'), name:'shopSn', width: 30},
            {title:WST.lang('store_account_number'), name:'loginName', width: 60},
			{title:WST.lang('brand_apply_shop_name'), name:'shopName',width: 80,sortable: true,renderer: function (val,item,rowIndex){
					return "<a href=\"javascript:toView(" + item['shopId'] + ",\'stopIndex\')\">"+item['shopName']+"</a>";
				}},
            {title:WST.lang('industry'), name:'tradeName',width: 80,sortable: true},
            {title:WST.lang('owner_name'), name:'shopkeeper',width: 40,hidden: true},
            {title:WST.lang('shopkeeper_contact_number'), name:'telephone',hidden: true},
            {title:WST.lang('shop_address'), name:'shopAddress',width:350 },
            {title:WST.lang('company'), name:'shopCompany',hidden: true },
            {title:WST.lang('op'), name:'' ,width:80, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
	            h += "<a class='btn btn-blue' href=\"javascript:toEdit(" + item['shopId'] + ",\'stopIndex\')\"><i class='fa fa-pencil'></i>"+WST.lang('edit')+"</a> ";
	            h += "<a class='btn btn-red' href='javascript:toDel(" + item['shopId'] + ",2)'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
	            return h;
            }}
            ];

    mmg = $('.mmg').mmGrid({height: (h-90),indexCol: true, cols: cols,method:'POST',nowrap:true,
        url: WST.U('admin/shops/pageStopQuery'), fullWidthRows: true, autoLoad: false,
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadStopGrid(p);
}
function loadStopGrid(p){
	var params = WST.getParams('.j-ipt');
	p=(p<=1)?1:p;
	params.areaIdPath = WST.ITGetAllAreaVals('areaId1','j-areas').join('_');
	params.page = p;
	mmg.load(params);
}
var initTab2 = false,initTab3 = false;
function initUpload(isEdit){
	if(!isEdit){
        legalCertificateImgUpload();
		businessLicenceImgUpload();
		bankAccountPermitImgUpload();
		organizationCodeUpload();
		taxRegistrationCertificateUpload();
		taxpayerQualificationUpload();
	}else{
		var element = layui.element;
		element.on('tab(msgTab)', function(data){
		   if(data.index==1){
		   	   if(initTab2)return;
		       initTab2 = true;
               legalCertificateImgUpload();
			   businessLicenceImgUpload();
			   bankAccountPermitImgUpload();
			   organizationCodeUpload();
		   }else if(data.index==2){
		   	   if(initTab3)return;
		       initTab3 = true;
               taxRegistrationCertificateUpload();
			   taxpayerQualificationUpload();
		   }
	    });
	}
}
function legalCertificateImgUpload (){
	WST.upload({
			pick:'#legalCertificateImgPicker',
			formData: {dir:'shops'},
			accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
			callback:function(f){
				var json = WST.toAdminJson(f);
				if(json.status==1){
				  	$('#legalCertificateImgMsg').empty().hide();
				    $('#legalCertificateImgPreview').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb).show();
				    $('#legalCertificateImgPreview_a').attr('href',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.name);
				    $('#legalCertificateImg').val(json.savePath+json.name);
				    $('#msg_legalCertificateImg').hide();
				}
			},
			progress:function(rate){
				$('#legalCertificateImgMsg').show().html(WST.lang('upload_rate')+rate+"%");
			}
		});
}
function businessLicenceImgUpload(){
	WST.upload({
			pick:'#businessLicenceImgPicker',
			formData: {dir:'shops'},
			accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
			callback:function(f){
				var json = WST.toAdminJson(f);
				if(json.status==1){
					$('#businessLicenceImgMsg').empty().hide();
					$('#businessLicenceImgPreview').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb).show();
					$('#businessLicenceImgPreview_a').attr('href',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.name);
					$('#businessLicenceImg').val(json.savePath+json.name);
					$('#msg_businessLicenceImg').hide();
				}
			},
			progress:function(rate){
				$('#businessLicenceImgMsg').show().html(WST.lang('upload_rate')+rate+"%");
			}
		});
}
function bankAccountPermitImgUpload(){
	WST.upload({
			pick:'#bankAccountPermitImgPicker',
			formData: {dir:'shops'},
			accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
			callback:function(f){
				var json = WST.toAdminJson(f);
				if(json.status==1){
					$('#bankAccountPermitImgMsg').empty().hide();
					$('#bankAccountPermitImgPreview').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb).show();
					$('#bankAccountPermitImgPreview_a').attr('href',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.name);
					$('#bankAccountPermitImg').val(json.savePath+json.name);
					$('#msg_bankAccountPermitImg').hide();
				}
			},
			progress:function(rate){
				$('#bankAccountPermitImgMsg').show().html(WST.lang('upload_rate')+rate+"%");
			}
		});
}
function organizationCodeUpload(){
	WST.upload({
			pick:'#organizationCodeImgPicker',
			formData: {dir:'shops'},
			accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
			callback:function(f){
				var json = WST.toAdminJson(f);
				if(json.status==1){
					$('#organizationCodeImgMsg').empty().hide();
					$('#organizationCodeImgPreview').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb).show();
					$('#organizationCodeImgPreview_a').attr('href',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.name);
					$('#organizationCodeImg').val(json.savePath+json.name);
					$('#msg_organizationCodeImg').hide();
				}
			},
			progress:function(rate){
				$('#organizationCodeImgMsg').show().html(WST.lang('upload_rate')+rate+"%");
			}
		});
}
function taxRegistrationCertificateUpload(){
	var uploader = WST.upload({
				pick:'#taxRegistrationCertificateImgPicker',
			    formData: {dir:'shops'},
				accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
				fileNumLimit:3,
				callback:function(f,file){
					var json = WST.toAdminJson(f);
					if(json.status==1){
					  	$('#taxRegistrationCertificateImgMsg').empty().hide();
					  	var tdiv = $("<div style='height:30px;float:left;margin:0px 5px;position:relative'><a target='_blank' href='"+WST.conf.RESOURCE_PATH+"/"+json.savePath+json.name+"'>"+
			                       "<img class='step_pic"+"' height='30' src='"+WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb+"' v='"+json.savePath+json.name+"'></a></div>");
						var btn = $('<div style="position: absolute;top: -5px;right: 0px;cursor: pointer;background: rgba(0,0,0,0.5);width: 18px;height: 18px;text-align: center;border-radius: 50%;" ><img src="'+WST.conf.ROOT+'/wstmart/home/View/default/img/seller_icon_error.png"></div>');
						tdiv.append(btn);
						$('#taxRegistrationCertificateImgBox').append(tdiv);
						$('#msg_taxRegistrationCertificateImg').hide();
						var imgPath = [];
						$('.step_pic').each(function(){
			                imgPath.push($(this).attr('v'));
						});
			            $('#taxRegistrationCertificateImg').val(imgPath.join(','));
						btn.on('click','img',function(){
						    uploader.removeFile(file);
						    $(this).parent().parent().remove();
						    uploader.refresh();
						    if($('#taxRegistrationCertificateImgBox').children().size()<=0){
						         $('#msg_taxRegistrationCertificateImg').show();
						    }
						});
					}else{
					  		 WST.msg(json.msg,{icon:2});
					}
				},
				progress:function(rate){
					$('#taxRegistrationCertificateImgMsg').show().html(WST.lang('upload_rate')+rate+"%");
				}
			});
}
function taxpayerQualificationUpload(){
	WST.upload({
			pick:'#taxpayerQualificationImgPicker',
			formData: {dir:'shops'},
			accept: {extensions: 'gif,jpg,jpeg,png',mimeTypes: 'image/jpg,image/jpeg,image/png,image/gif'},
			callback:function(f){
				var json = WST.toAdminJson(f);
				if(json.status==1){
					$('#taxpayerQualificationImgMsg').empty().hide();
					$('#taxpayerQualificationImgPreview').attr('src',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.thumb).show();
					$('#taxpayerQualificationImgPreview_a').attr('href',WST.conf.RESOURCE_PATH+"/"+json.savePath+json.name);
					$('#taxpayerQualificationImg').val(json.savePath+json.name);
					$('#msg_taxpayerQualificationImg').hide();
				}
			},
			progress:function(rate){
				$('#taxpayerQualificationImgMsg').show().html(WST.lang('upload_rate')+rate+"%");
			}
	});
}

function delVO(obj){
	$(obj).parent().remove();
	var selector = $(obj).attr('selector');
	var imgPath = [];
	$('.'+selector+'_step_pic').each(function(){
		imgPath.push($(this).attr('v'));
	});
	$('#'+selector).val(imgPath.join(','));
}
function toEdit(id,src){
	location.href=WST.U('admin/shops/toEdit','id='+id+'&p='+WST_CURR_PAGE+'&src='+src);
}
function toView(id,src){
	location.href=WST.U('admin/shops/toView','id='+id+'&p='+WST_CURR_PAGE+'&src='+src);
}
function toDel(id,type){
	var box = WST.confirm({content:WST.lang('are_you_sure_you_want_to_delete_it'),yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           $.post(WST.U('admin/shops/del'),{id:id},function(data,textStatus){
	           			  layer.close(loading);
	           			  var json = WST.toAdminJson(data);
	           			  if(json.status=='1'){
	           			    	WST.msg(WST.lang('op_ok'),{icon:1});
	           			    	layer.close(box);
	           			    	if(type==1){
                                    loadGrid(WST_CURR_PAGE);
								}else{
                                    loadStopGrid(WST_CURR_PAGE)
								}

	           			  }else{
	           			    	WST.msg(json.msg,{icon:2});
	           			  }
	           		});
	            }});
}
function checkLoginKey(obj){
	if($.trim(obj.value)=='')return;
	var params = {key:obj.value,userId:0};
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/users/checkLoginKey'),params,function(data,textStatus){
    	layer.close(loading);
    	var json = WST.toAdminJson(data);
    	if(json.status!='1'){
    		WST.msg(json.msg,{icon:2});
    		obj.value = '';
    	}
    });
}
function save(p,src){
	$('#editFrom').isValid(function(v){
		if(v){
			var params = WST.getParams('.ipt');
			var params = WST.getParams('.a-ipt');
            $("select[class^='j-']").each(function(idx,item){
                var fieldName = $(item).attr('data-name');
                params[fieldName] = WST.ITGetAreaVal('j-'+fieldName);
            });
			var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		    $.post(WST.U('admin/shops/edit'),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toAdminJson(data);
		    	if(json.status=='1'){
		    		WST.msg(WST.lang('op_ok'),{icon:1,time:1000},function(){
		    			if(params.shopStatus==1){
			    			location.href=WST.U('admin/shops/index','p='+p);
			    		}else{
                            location.href=WST.U('admin/shops/stopIndex','p='+p);
			    		}
		    		});

		    	}else{
		    		WST.msg(json.msg,{icon:2});
		    	}
		    });
		}
	});
}
function getUserByKey(){
	if($.trim($('#keyName').val())=='')return;
	$('#keyNameBox').html('');
	$('#shopUserId').val(0);
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/users/getUserByKey'),{key:$('#keyName').val()},function(data,textStatus){
		layer.close(loading);
		var json = WST.toAdminJson(data);
		if(json.status=='1'){
		    $('#keyNameBox').html(WST.lang('label_goodsappraises_user')+'：'+json.data.loginName);
		    $('#shopUserId').val(json.data.userId);
		}else{
		    WST.msg(json.msg,{icon:2});
		}
    });
}
function add(p,src){
	$('#editFrom').isValid(function(v){
		if(v){
			var params = WST.getParams('.a-ipt');
            $("select[class^='j-']").each(function(idx,item){
                var fieldName = $(item).attr('data-name');
                params[fieldName] = WST.ITGetAreaVal('j-'+fieldName);
            });
			var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		    $.post(WST.U('admin/shops/add'),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toAdminJson(data);
		    	if(json.status=='1'){
		    		WST.msg(WST.lang('op_ok'),{icon:1,time:1000},function(){
			    		location.href=WST.U('admin/shops/'+src,'p='+p);
		    		});
		    	}else{
		    		WST.msg(json.msg,{icon:2});
		    	}
		    });
		}
	});
}

function apply(p){
	$('#editFrom').isValid(function(v){
		if(v){
			var params = WST.getParams('.a-ipt');
            $("select[class^='j-']").each(function(idx,item){
                var fieldName = $(item).attr('data-name');
                params[fieldName] = WST.ITGetAreaVal('j-'+fieldName);
            });
			if(params.applyStatus==-1 && params.applyDesc==''){
				 WST.msg(WST.lang('please_enter_the_reason_for_not_passing_the_audit'),{icon:2});
				 return;
			}
			var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
		    $.post(WST.U('admin/shops/handleApply'),params,function(data,textStatus){
		    	layer.close(loading);
		    	var json = WST.toAdminJson(data);
		    	if(json.status=='1'){
		    		WST.msg(WST.lang('op_ok'),{icon:1,time:1000},function(){
			    		location.href=WST.U('admin/shops/apply',"p="+p);
		    		});
		    	}else{
		    		WST.msg(json.msg,{icon:2});
		    	}
		    });
		}
	});
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
var container,map,label,marker,mapLevel = 15;
function initQQMap(longitude,latitude,mapLevel){
    var container = document.getElementById("container");
    mapLevel = WST.blank(mapLevel,13);
    var mapopts,center = null;
    mapopts = {zoom: parseInt(mapLevel)};
	map = new qq.maps.Map(container, mapopts);
	if(WST.blank(longitude)=='' || WST.blank(latitude)==''){
		var cityservice = new qq.maps.CityService({
		    complete: function (result) {
		        map.setCenter(result.detail.latLng);
		    }
		});
		cityservice.searchLocalCity();
	}else{
        marker = new qq.maps.Marker({
            position:new qq.maps.LatLng(latitude,longitude),
            map:map
        });
        map.panTo(new qq.maps.LatLng(latitude,longitude));
	}
	var url3;
	qq.maps.event.addListener(map, "click", function (e) {
		if(marker)marker.setMap(null);
		marker = new qq.maps.Marker({
            position:e.latLng,
            map:map
        });
	    $('#latitude').val(e.latLng.getLat().toFixed(6));
	    $('#longitude').val(e.latLng.getLng().toFixed(6));
	    url3 = encodeURI(window.conf.__HTTP__+'apis.map.qq.com/ws/geocoder/v1/?location=' + e.latLng.getLat() + "," + e.latLng.getLng() + "&key="+window.conf.MAP_KEY+"&output=jsonp&&callback=?");
	    $.getJSON(url3, function (result) {
	        if(result.result!=undefined){
	            document.getElementById("shopAddress").value = result.result.address;
	        }else{
	            document.getElementById("shopAddress").value = "";
	        }

	    })
	});
	qq.maps.event.addListener(map,'zoom_changed',function() {
        $('#mapLevel').val(map.getZoom());
    });
}
function mapCity(obj){
    var citys = [];
    $('.j-'+$(obj).attr('data-name')).each(function(){
        citys.push($(this).find('option:selected').text());
    })
    if(citys.length==0)return;
    var url2 = encodeURI(window.conf.__HTTP__+'apis.map.qq.com/ws/geocoder/v1/?region=' + citys.join('') + "&address=" + citys.join('') + "&key="+window.conf.MAP_KEY+"&output=jsonp&&callback=?");
    $.getJSON(url2, function (result) {
        if(result.result.location){
            map.setCenter(new qq.maps.LatLng(result.result.location.lat, result.result.location.lng));
            map.setZoom(mapLevel);
        }
    });
}

/**********移动端商家申请*************/
function initApplyGrid2(p){
    var h = WST.pageHeight();
    var cols = [
            {title:WST.lang('apply_for_an_account_number'), name:'loginName', width: 60, renderer: function(val,item,rowIndex){
            	return WST.blank(item['userName'])+"【"+item['loginName']+"】";
            }},
            {title:WST.lang('label_orderrefunds_apply_user'), name:'linkman', width: 60},
			{title:WST.lang('config_mall_tel'), name:'linkPhone', width: 60},
			{title:WST.lang('business_scope'), name:'applyIntention', width: 350, renderer: function(val,item,rowIndex){return '<div title="'+val+'">'+val+'</div>'}},
			{title:WST.lang('date_of_application'), name:'createTime', width: 80},
			{title:WST.lang('application_status'), name:'applyStatus', width: 30, renderer: function(val,item,rowIndex){
				if(item['applyStatus']==1){
                    return "<span class='statu-yes'><i class='fa fa-check-circle'></i> "+WST.lang('the_application_passed')+"</span>";
	        	}else if(item['applyStatus']==-1){
	        		return "<span class='statu-no'><i class='fa fa-ban'></i> "+WST.lang('application_failed')+"</span>";
	        	}else{
                    return "<span class='statu-wait'><i class='fa fa-clock-o'></i> "+WST.lang('label_cashdraws_status2')+"</span>"
	        	}
			}},
			{title:WST.lang('brand_apply_shop_name'), name:'shopName', width: 60},
            {title:WST.lang('op'), name:'' ,width:190, align:'center', renderer: function(val,item,rowIndex){
                var h = "";
                h += "<a  class='btn btn-blue' onclick='javascript:toView2("+item['id']+")'><i class='fa fa-search'></i>"+WST.lang("view")+"</a> ";
	            if(item['applyStatus']==0 && WST.GRANT.DPSQ_04)h += "<a  class='btn btn-blue' onclick='javascript:toEditApply2("+item['id']+")'><i class='fa fa-pencil'></i>"+WST.lang('handle')+"</a> ";
				if(WST.GRANT.DPSQ_03)h += "<a  class='btn btn-red' onclick='javascript:toDelApply2(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('del')+"</a> ";
                return h;
            }}
            ];

    mmg2 = $('#mmg2').mmGrid({height: h-135,indexCol: true,indexColWidth:50, cols: cols,method:'POST',nowrap:true,
        url: WST.U('admin/shopapplys/pageQuery'), fullWidthRows: true, autoLoad: false,remoteSort: true,sortName:'',sortStatus:'desc',
        plugins: [
            $('#pg2').mmPaginator({})
        ]
    });
    loadApplyGrid2(p);
}
function loadApplyGrid2(p){
	p=(p<=1)?1:p;
	var params = WST.getParams('.ja-ipt');
	params.page = p;
	mmg2.load(params);
}
function toEditApply2(id){
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/shopapplys/getById'),{id:id},function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.id){
        	$('#applyStatus0').attr('checked',false);
        	$('#applyStatus1').attr('checked',false);
        	$('.applyStatusTr0').css('display','none');
        	$('.applyStatusTr1').css('display','none');
        	layui.form.render('radio','applyStatusBox');
        	layui.form.on('radio(applyStatus)', function(data){
			    WST.showHide((data.value==1)?0:1,'.applyStatusTr0');
			    WST.showHide((data.value==1)?1:0,'.applyStatusTr1');
			});
        	$('#loginName').html(WST.blank(json['userName'])+"【"+json['loginName']+"】");
        	$('#linkman').html(json.linkman);
        	$('#linkPhone').html(json.linkPhone);
        	$('#applyIntention').html(json.applyIntention);
           	var box = WST.open({title:WST.lang('application_for_business_settlement'),type:1,content:$('#applyBox'),area: ['600px', '560px'],btn: [WST.lang('confirm'),WST.lang('cancel')],yes:function(){
			    $('#applyForm').isValid(function(v){
                    if(v){
						var params = WST.getParams('.eipt');
						params.id = id;
						var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
						$.post(WST.U('admin/shopapplys/handleApply'),params,function(data,textStatus){
						   	layer.close(loading);
						   	var json = WST.toAdminJson(data);
						   	if(json.status=='1'){
						   		WST.msg(WST.lang('op_ok'),{icon:1});
						   		$('#applyBox').hide();
						   		$('#applyForm')[0].reset();
						   		layer.close(box);
				                loadApplyGrid2(WST_CURR_PAGE)
						   	}else{
						   		WST.msg(json.msg,{icon:2});
						   	}
						});
				    }
		        });
	        },cancel:function(){$('#applyBox').hide();},end:function(){$('#applyBox').hide();}});
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}
function toView2(id){
	var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
    $.post(WST.U('admin/shopapplys/getById'),{id:id},function(data,textStatus){
        layer.close(loading);
        var json = WST.toAdminJson(data);
        if(json.id){
        	$('#vloginName').html(WST.blank(json['userName'])+"【"+json['loginName']+"】");
        	$('#vlinkman').html(json.linkman);
        	$('#vlinkPhone').html(json.linkPhone);
        	$('#vapplyIntention').html(json.applyIntention);
        	$('#vapplyStatus').html((json.applyStatus=='0')?WST.lang('to_be_reviewed'):((json.applyStatus=='1')?WST.lang('the_application_passed'):WST.lang('application_failed')));
        	if(json.applyStatus==0){
               WST.showHide(0,'.vapplyStatusTr0,.vapplyStatusTr1');
        	}else{
	        	WST.showHide((json.applyStatus==1)?0:1,'.vapplyStatusTr0');
	        	WST.showHide((json.applyStatus==1)?1:0,'.vapplyStatusTr1');
        	}
        	$('#vhandleReamrk').html(json.handleReamrk);
        	$('#vshopName').html(json.shopName);
           	var box = WST.open({title:WST.lang('application_for_business_settlement'),type:1,content:$('#applyBox2'),area: ['600px', '500px'],end:function(){$('#applyBox2').hide();}});
        }else{
            WST.msg(json.msg,{icon:2});
        }
    });
}
function toDelApply2(id){
  var msg = WST.lang('are_you_sure_you_want_to_delete_it');
	var box = WST.confirm({content:msg,yes:function(){
	           var loading = WST.msg(WST.lang('loading'), {icon: 16,time:60000});
	           	$.post(WST.U('admin/shopapplys/del'),{id:id},function(data,textStatus){
	           		layer.close(loading);
	           		var json = WST.toAdminJson(data);
	           		if(json.status=='1'){
	           			WST.msg(WST.lang('op_ok'),{icon:1});
	           			   layer.close(box);
	           		       loadApplyGrid2(WST_CURR_PAGE);
	           			}else{
	           			   WST.msg(json.msg,{icon:2});
	           			}
	           		});
	}});
}
