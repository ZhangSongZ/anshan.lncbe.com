define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/prohs/index' + location.search,
                    add_url: 'product/prohs/add',
                    edit_url: 'product/prohs/edit',
                    del_url: 'product/prohs/del',
                    multi_url: 'product/prohs/multi',
                    import_url: 'product/prohs/import',
                    table: 'dic_hscode',
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
                        {field: 'hscode', title: __('Hscode'), operate: 'LIKE'},
                        {field: 'hsname', title: __('Hsname'), operate: 'LIKE'},
                        // {field: 'unit', title: __('Unit'), operate: 'LIKE'},
                        {field: 'unit1', title: __('Unit1'), operate: 'LIKE'},
                        {field: 'unit2', title: __('Unit2'), operate: 'LIKE'},
                        // {field: 'tax_code', title: __('Tax_code'), operate: 'LIKE'},
                        {field: 'excise_tax', title: __('Excise_tax'), operate:'BETWEEN'},
                        {field: 'increment_tax', title: __('Increment_tax'), operate:'BETWEEN'},
                        {field: 'consolidated_tax', title: __('Consolidated_tax'), operate:'BETWEEN'},
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
