define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'company/ecompany/index' + location.search,
                    add_url: 'company/ecompany/add',
                    edit_url: 'company/ecompany/edit',
                    del_url: 'company/ecompany/del',
                    multi_url: 'company/ecompany/multi',
                    import_url: 'company/ecompany/import',
                    table: 'ecompany',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'ecID',
                sortName: 'ecID',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'ecID', title: __('EcID')},
                        {field: 'ebcCode', title: __('EbcCode'), operate: 'LIKE'},
                        {field: 'ebcName', title: __('EbcName'), operate: 'LIKE'},
                        {field: 'social_code', title: __('Social_code'), operate: 'LIKE'},
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
