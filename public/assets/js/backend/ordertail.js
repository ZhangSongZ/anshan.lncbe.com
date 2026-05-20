define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ordertail/index' + location.search,
                    add_url: 'ordertail/add',
                    edit_url: 'ordertail/edit',
                    del_url: 'ordertail/del',
                    multi_url: 'ordertail/multi',
                    import_url: 'ordertail/import',
                    table: 'excbe_orderdetail',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'item_id',
                sortName: 'item_id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'item_id', title: __('Item_id')},
                        {field: 'orderNo', title: __('Orderno'), operate: 'LIKE'},
                        {field: 'product_bn', title: __('Product_bn'), operate: 'LIKE'},
                        {field: 'product_name', title: __('Product_name'), operate: 'LIKE'},
                        {field: 'gcode', title: __('Gcode'), operate: 'LIKE'},
                        {field: 'qty', title: __('Qty')},
                        {field: 'total', title: __('Total'), operate:'BETWEEN'},
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
