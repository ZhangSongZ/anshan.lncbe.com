define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ordermain/index' + location.search,
                    add_url: 'ordermain/add',
                    edit_url: 'ordermain/edit',
                    del_url: 'ordermain/del',
                    multi_url: 'ordermain/multi',
                    import_url: 'ordermain/import',
                    table: 'excbe_ordermain',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'orderNo',
                sortName: 'orderNo',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'orderNo', title: __('Orderno'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'copNo', title: __('Copno'), operate: 'LIKE'},
                        {field: 'ie_date', title: __('Ie_date'), operate: 'LIKE'},
                        {field: 'totalPackageNo', title: __('Totalpackageno'), operate: 'LIKE'},
                        {field: 'pack_no', title: __('Pack_no'), operate: 'LIKE'},
                        {field: 'cost_item', title: __('Cost_item'), operate:'BETWEEN'},
                        {field: 'cost_freight', title: __('Cost_freight'), operate:'BETWEEN'},
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
