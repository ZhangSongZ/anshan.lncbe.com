define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datadictionary/wraptype/index' + location.search,
                    add_url: 'datadictionary/wraptype/add',
                    edit_url: 'datadictionary/wraptype/edit',
                    del_url: 'datadictionary/wraptype/del',
                    multi_url: 'datadictionary/wraptype/multi',
                    import_url: 'datadictionary/wraptype/import',
                    table: 'dic_wraptype',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'traID',
                sortName: 'traID',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'traID', title: __('TraID')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
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
