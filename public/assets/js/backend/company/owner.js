define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'company/owner/index' + location.search,
                    add_url: 'company/owner/add',
                    edit_url: 'company/owner/edit',
                    del_url: 'company/owner/del',
                    multi_url: 'company/owner/multi',
                    import_url: 'company/owner/import',
                    table: 'owner',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'ownerid',
                sortName: 'ownerid',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'ownerid', title: __('Ownerid')},
                        {field: 'ownercode', title: __('Ownercode'), operate: 'LIKE'},
                        {field: 'ownername', title: __('Ownername'), operate: 'LIKE'},
                        {field: 'owner_scc', title: __('Owner_scc'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
