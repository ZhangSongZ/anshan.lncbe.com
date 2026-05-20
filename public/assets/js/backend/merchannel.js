define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'merchannel/index' + location.search,
                    add_url: 'merchannel/add',
                    edit_url: 'merchannel/edit',
                    del_url: 'merchannel/del',
                    multi_url: 'merchannel/multi',
                    import_url: 'merchannel/import',
                    table: 'Merchannel',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        // {field: 'auth_id', title: __('Auth_id')},
                        {field: 'admin.nickname', title: __('Admin.nickname'), operate: 'LIKE'},
                        {field: 'category', title: __('Category')},
                        {field: 'order', title: __('Order')},
                        {field: 'waybill', title: __('Waybill')},
                        {field: 'list', title: __('List')},
                        {field: 'total', title: __('Total')},
                        {field: 'arrival', title: __('Arrival')},
                        {field: 'departure', title: __('Departure')},
                        {field: 'revoke', title: __('Revoke')},
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
