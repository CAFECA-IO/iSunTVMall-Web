function userAddrEditInit() {
    /* 表单验证 */
    $('#invoiceForm').validator({
        fields: {
            invoiceType: {
                rule: "checked;",
                msg: { checked: WST.lang('require_less_one') },
                tip: "",
                ok: "",
            },
            invoiceHead: {
                rule: "required;",
                msg: { required: WST.lang('require_invoice_head') },
                tip: WST.lang('require_invoice_head'),
                ok: "",
            },
            invoiceCode: {
                rule: "required;",
                msg: { required: WST.lang('require_invoice_tax_number') },
                tip: WST.lang('require_invoice_tax_number'),
                ok: "",
            },
            invoiceAddr: {
                rule: "required(#invoiceType-1:checked);",
                msg: { required: WST.lang('require_invoice_address') },
                tip: WST.lang('require_invoice_address'),
                ok: "",
            },
            invoicePhoneNumber: {
                rule: "required(#invoiceType-1:checked);",
                msg: { required: WST.lang('require_invoice_telphone') },
                tip: WST.lang('require_invoice_telphone'),
                ok: "",
            },
            invoiceBankName: {
                rule: "required(#invoiceType-1:checked);",
                msg: { required: WST.lang('require_card_open_bank') },
                tip: WST.lang('require_card_open_bank'),
                ok: "",
            },
            invoiceBankNo: {
                rule: "required(#invoiceType-1:checked);",
                msg: { required: WST.lang('require_bank_account') },
                tip: WST.lang('require_bank_account'),
                ok: "",
            }
        },
        valid: function (form) {
            var params = WST.getParams('.ipt');
            var loading = WST.msg(WST.lang('submiting_tips'), { icon: 16, time: 60000 });
            $.post(WST.U('home/invoices/' + ((params.id == 0) ? "add" : "edit")), params, function (data, textStatus) {
                layer.close(loading);
                var json = WST.toJson(data);
                if (json.status == '1') {
                    WST.msg(json.msg, { icon: 1 });
                    location.href = WST.U('home/invoices/invoicelist');
                } else {
                    WST.msg(json.msg, { icon: 2 });
                }
            });

        }

    });
}
function listQuery() {
    $.post(WST.U('home/invoices/pageQuery'), '', function (data, textStatus) {
        var json = WST.toJson(data);
        if (json && json.length>0) {
            var gettpl = document.getElementById('invoices').innerHTML;
            laytpl(gettpl).render(json, function (html) {
                $('#invoices_box').html(html);
            });
        } else {
            $('#invoices_box').empty();
        }
    });
}

function editInvoice(id) {
    location.href = WST.U('home/invoices/toEdit', 'id=' + id);
}
function toAdd(){
    var num = $('#invoices_box').children().size();
    if(num<20){
        location.href = WST.U('home/invoices/toEdit');
    }else{
        WST.msg(WST.lang('require_invoice_add_limit'), { icon: 5 });
    }
}
function delInvoice(id, t) {
    WST.confirm({
        content: WST.lang('confirm_del_inquiry'), yes: function (tips) {
            var ll = layer.load(WST.lang('submiting_tips'));
            $.post(WST.U('Home/invoices/del'), { id: id }, function (data, textStatus) {
                layer.close(ll);
                layer.close(tips);
                var json = WST.toJson(data);
                if (json.status == '1') {
                    WST.msg(WST.lang('operation_success'), { icon: 1 }, function () {
                        listQuery();
                    });
                } else {
                    WST.msg(WST.lang('operation_fail'), { icon: 5 });
                }
            });
        }
    });

}