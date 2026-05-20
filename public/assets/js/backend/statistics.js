define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'statistics/index' + location.search,
                    add_url: 'statistics/add',
                    edit_url: 'statistics/edit',
                    del_url: 'statistics/del',
                    multi_url: 'statistics/multi',
                    import_url: 'statistics/import',
                    table: 'statistics',
                }
            });

            var table = $("#table");
             table.on('load-success.bs.table', function (e, data) {
                //这里我们手动设置底部的值
                $("#count").text(data.extend.count);
                $("#counts").text(data.extend.counts);
            
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'total', title: __('Total'), operate: 'LIKE'},
                        {field: 'type', title: __('Type'), operate: 'LIKE'},
                        {field: 'tracking', title: __('Tracking'), operate: 'LIKE'},
                        {field: 'amount', title: __('贸易额（美元）'), operate:'BETWEEN'},
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
