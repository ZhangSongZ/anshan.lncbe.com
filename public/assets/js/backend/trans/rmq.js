define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'trans/rmq/index' + location.search,
                    add_url: 'trans/rmq/add',
                    edit_url: 'trans/rmq/edit',
                    del_url: 'trans/rmq/del',
                    multi_url: 'trans/rmq/multi',
                    import_url: 'trans/rmq/import',
                    table: 'rmq',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'rmq_id',
                sortName: 'rmq_id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'rmq_id', title: __('Rmq_id')},
                        {field: 'ebcname', title: __('Ebcname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'host', title: __('Host'), operate: 'LIKE'},
                        {field: 'port', title: __('Port')},
                        {field: 'user', title: __('User'), operate: 'LIKE'},
                        {field: 'pwd', title: __('Pwd'), operate: 'LIKE'},
                        {field: 'vhost', title: __('Vhost'), operate: 'LIKE'},
                        {field: 'exchangeN', title: __('ExchangeN'), operate: 'LIKE'},
                        {field: 'queueNsend', title: __('QueueNsend'), operate: 'LIKE'},
                        {field: 'queueNreceive', title: __('QueueNreceive'), operate: 'LIKE'},
                        {field: 'note', title: __('Note'), operate: 'LIKE'},
                        {field: 'dxpId', title: __('DxpId'), operate: 'LIKE'},
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
