define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datadictionary/currency/index' + location.search,
                    add_url: 'datadictionary/currency/add',
                    edit_url: 'datadictionary/currency/edit',
                    del_url: 'datadictionary/currency/del',
                    multi_url: 'datadictionary/currency/multi',
                    import_url: 'datadictionary/currency/import',
                    table: 'dic_currency',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'curID',
                sortName: 'curID',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'curID', title: __('CurID')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'Ecode', title: __('Ecode'), operate: 'LIKE'},
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
