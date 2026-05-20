define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'company/operator/index' + location.search,
                    add_url: 'company/operator/add',
                    edit_url: 'company/operator/edit',
                    del_url: 'company/operator/del',
                    multi_url: 'company/operator/multi',
                    import_url: 'company/operator/import',
                    table: 'operator',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'opeID',
                sortName: 'opeID',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'opeID', title: __('OpeID')},
                        {field: 'operatorCode', title: __('OperatorCode'), operate: 'LIKE'},
                        {field: 'operatorName', title: __('OperatorName'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'loctNo', title: __('LoctNo'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
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
