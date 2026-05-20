define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datadictionary/trafmode/index' + location.search,
                    add_url: 'datadictionary/trafmode/add',
                    edit_url: 'datadictionary/trafmode/edit',
                    del_url: 'datadictionary/trafmode/del',
                    multi_url: 'datadictionary/trafmode/multi',
                    import_url: 'datadictionary/trafmode/import',
                    table: 'dic_trafmode',
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
