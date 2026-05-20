define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'basic/index' + location.search,
                    add_url: 'basic/add',
                    edit_url: 'basic/edit',
                    del_url: 'basic/del',
                    multi_url: 'basic/multi',
                    import_url: 'basic/import',
                    table: 'basic',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 2,
                
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        {field: 'admin.username', title: __('Admin.username'), operate: 'LIKE'},
                        {field: 'ebpCode', title: __('EbpCode'), operate: 'LIKE'},
                        {field: 'ebpName', title: __('EbpName'), operate: 'LIKE'},
                        {field: 'ebcCode', title: __('EbcCode'), operate: 'LIKE'},
                        {field: 'ebcName', title: __('EbcName'), operate: 'LIKE'},
                        {field: 'logisticsCode', title: __('LogisticsCode'), operate: 'LIKE'},
                        {field: 'logisticsName', title: __('LogisticsName'), operate: 'LIKE'},
                        {field: 'customsCode', title: __('CustomsCode'), operate: 'LIKE'},
                        {field: 'portCode', title: __('PortCode'), operate: 'LIKE'},
                        {field: 'statisticsFlag', title: __('StatisticsFlag'), operate: 'LIKE', formatter: Table.api.formatter.flag},
                        {field: 'agentCode', title: __('AgentCode'), operate: 'LIKE'},
                        {field: 'agentName', title: __('AgentName'), operate: 'LIKE'},
                        {field: 'ownerCode', title: __('OwnerCode'), operate: 'LIKE'},
                        {field: 'ownerName', title: __('OwnerName'), operate: 'LIKE'},
                        {field: 'tradeMode', title: __('TradeMode'), operate: 'LIKE'},          
                        {field: 'trafMode', title: __('TrafMode'), operate: 'LIKE'},
                        
                        {field: 'decBillNo', title: __('分单模式'), operate: 'LIKE'},
                        {field: 'declagAgentCode', title: __('报关单位代码'), operate: 'LIKE'},
                        {field: 'declAgentName', title: __('报关单位名称'), operate: 'LIKE'},
                        
                        {field: 'operatorCode', title: __('监管场所经营人代码'), operate: 'LIKE'},
                        {field: 'operatorName', title: __('监管场所经营人名称'), operate: 'LIKE'},
                        {field: 'loctNo', title: __('监管场所代码'), operate: 'LIKE'},
                     
                        
                         {field: 'status', title: __('Status'),searchList:{1:'启用',0:'关闭'},formatter: function (value, row, index) {
                                return row.issystem ? "-" : Table.api.formatter.toggle.call(this, value, row, index);
                            }},
                        
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
