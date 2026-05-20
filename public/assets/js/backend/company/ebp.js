define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'company/ebp/index' + location.search,
                    add_url: 'company/ebp/add',
                    edit_url: 'company/ebp/edit',
                    del_url: 'company/ebp/del',
                    multi_url: 'company/ebp/multi',
                    import_url: 'company/ebp/import',
                    table: 'ebp',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'ebpID',
                sortName: 'ebpID',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'ebpID', title: __('EbpID')},
                        {field: 'ebpCode', title: __('EbpCode'), operate: 'LIKE'},
                        {field: 'ebpName', title: __('EbpName'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'appUname', title: __('AppUname'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'appUid', title: __('AppUid'), operate: 'LIKE'},
                        {field: 'social_code', title: __('Social_code'), operate: 'LIKE'},
                        {field: 'isDefault', title: __('IsDefault'), operate: 'LIKE'},
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
