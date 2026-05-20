define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'company/agent/index' + location.search,
                    add_url: 'company/agent/add',
                    edit_url: 'company/agent/edit',
                    del_url: 'company/agent/del',
                    multi_url: 'company/agent/multi',
                    import_url: 'company/agent/import',
                    table: 'agent',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'agentID',
                sortName: 'agentID',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'agentID', title: __('AgentID')},
                        {field: 'agentCode', title: __('AgentCode'), operate: 'LIKE'},
                        {field: 'agentName', title: __('AgentName'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'dxpID', title: __('DxpID'), operate: 'LIKE'},
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
