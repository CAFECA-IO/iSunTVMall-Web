var mmg;
function initGrid() {
    var h = WST.pageHeight();
    var cols = [
        { title: WST.lang('wstim_username'), name: 'loginName'},
        { title: WST.lang('wstim_tip33'), name: 'startTime'},
        { title: WST.lang('wstim_tip34'), name: 'platform', renderer:function(val){
            var code = WST.lang('wstitm_unknown');
            switch(val){
                case 1:
                    code = 'PC';
                break;
                case 2:
                    code = WST.lang('wstim_tip35');
                break;
                case 3:
                    code = 'android';
                break;
                case 4:
                    code = 'ios';
                break;
                case 5:
                    code = WST.lang('wstim_tip36');
                break;
            }
            return code;
        }},
        { title: WST.lang('wstim_tip37'), name: 'ip'},
        { title: WST.lang('wstim_tip38'), name: 'stayTime'},
    ];

    mmg = $('.mmg').mmGrid({
        height: h - 46, indexCol: true, indexColWidth: 50, cols: cols, method: 'POST', nowrap: true,
        url: APIS['shopStatisticsQuery'], fullWidthRows: true, autoLoad: false, remoteSort: true, sortName: '', sortStatus: 'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid();
}
function loadGrid() {
    mmg.load();
}
