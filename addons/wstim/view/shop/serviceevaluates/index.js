var mmg;
function initGrid() {
    var h = WST.pageHeight();
    var cols = [
        { title: WST.lang('wstim'), name: 'loginName'},
        { title: WST.lang('wstim_total_score'), name: 'score'},
    ];

    mmg = $('.mmg').mmGrid({
        height: h - 46, indexCol: true, indexColWidth: 50, cols: cols, method: 'POST', nowrap: true,
        url: APIS['shopEvalQuery'], fullWidthRows: true, autoLoad: false, remoteSort: true, sortName: '', sortStatus: 'desc',
        plugins: [
            $('#pg').mmPaginator({})
        ]
    });
    loadGrid();
}
function loadGrid() {
    mmg.load();
}
