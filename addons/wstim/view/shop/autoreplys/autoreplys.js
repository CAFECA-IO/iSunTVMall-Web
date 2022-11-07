var mmg;
function initGrid() {
    var h = WST.pageHeight();
    var cols = [
        { title: WST.lang('wstim_tip25'), name: 'keyword', width: 100 },
        { title: WST.lang('wstim_tip24'), name: 'replyContent', width: 300 },
        {
            title: WST.lang('wstim_op'), name: '', width: 150, align: 'center', renderer: function (val, item, rowIndex) {
                var h = "";
                h += "<a  class='btn btn-blue' onclick='javascript:toEdit(" + item['id'] + ")'><i class='fa fa-pencil'></i>"+WST.lang('wstim_edit')+"</a> ";
                h += "<a  class='btn btn-red' onclick='javascript:toDel(" + item['id'] + ")'><i class='fa fa-trash-o'></i>"+WST.lang('wstim_del')+"</a> ";
                return h;
            }
        }
    ];

    mmg = $('.mmg').mmGrid({
        height: h - 82, indexCol: true, indexColWidth: 50, cols: cols, method: 'POST', nowrap: true,
        url: APIS['shopAutoReplyQuery'], fullWidthRows: true, autoLoad: false, remoteSort: true, sortName: '', sortStatus: 'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid();
}
function loadGrid() {
    mmg.load();
}


function toEdit(id) {
    location.href = APIS['shopAutoReplyToEdit'] + '?id=' + id;
}

function save() {
    var val = $('#reply_content').val();
    var keyword = $('#keyword').val();
    var loading = layer.msg(WST.lang('wstim_loading'), { icon: 16, time: 60000 });
    $.post(APIS['shopAutoReplyEdit'], { id: id, replyContent: val, keyword: keyword }, function (json) {
        layer.close(loading);
        if (json.status == '1') {
            layer.msg(json.msg, { icon: 1 });
            location.href = APIS['shopAutoReplyIndex'];
        } else {
            layer.msg(json.msg, { icon: 2 });
        }
    });
}

function toDel(id) {
    layer.confirm(WST.lang('wstim_are_you_del'), {
        btn: [WST.lang('wstim_ok'), WST.lang('wstim_cancel')]
    }, function () {
        var loading = layer.msg(WST.lang('wstim_loading'), { icon: 16, time: 60000 });
        $.post(APIS['shopAutoReplyDel'], { id: id }, function (json, textStatus) {
            layer.close(loading);
            if (json.status == '1') {
                loadGrid();
                layer.msg(WST.lang('op_ok'), { icon: 1 });
            } else {
                layer.msg(json.msg, { icon: 2 });
            }
        });


    });
}