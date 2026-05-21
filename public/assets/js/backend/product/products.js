define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'product/products/index' + location.search,
                    add_url: 'product/products/add',
                    edit_url: 'product/products/edit',
                    del_url: 'product/products/del',
                    multi_url: 'product/products/multi',
                    import_url: 'product/products/import',
                    table: 'products',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'productID',
                sortName: 'productID',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'productID', title: __('序号')},
                        {field: 'auth_id', title: __('企业ID')},
                        {field: 'itemNo', title: __('ItemNo'), operate: 'LIKE'},
                        {field: 'itemName', title: __('ItemName'), operate: 'LIKE'},
                        {field: 'EnName', title: __('EnName'), operate: 'LIKE'},
                        {field: 'gcode', title: __('Gcode'), operate: 'LIKE'},
                        // {field: 'gname', title: __('Gname'), operate: 'LIKE'},
                        {field: 'gmodel', title: __('Gmodel'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'barcode', title: __('Barcode'), operate: 'LIKE'},
                        {field: 'brand', title: __('Brand'), operate: 'LIKE'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        // {field: 'declarePrice', title: __('DeclarePrice'), operate:'BETWEEN'},
                        {field: 'product_currency', title: __('Product_currency')},
                        // {field: 'taxno', title: __('Taxno'), operate: 'LIKE'},
                        // {field: 'giftFlag', title: __('GiftFlag'), searchList: {"0":__('GiftFlag 0'),"1":__('GiftFlag 1')}, formatter: Table.api.formatter.flag},
                        // {field: 'returnStatus', title: __('ReturnStatus'), searchList: {"none":__('None'),"-1":__('ReturnStatus -1'),"1":__('ReturnStatus 1'),"2":__('ReturnStatus 2'),"3":__('ReturnStatus 3'),"4":__('ReturnStatus 4'),"100":__('ReturnStatus 100'),"120":__('ReturnStatus 120'),"399":__('ReturnStatus 399'),"5":__('ReturnStatus 5')}, formatter: Table.api.formatter.status},
                        // {field: 'returnTime', title: __('ReturnTime'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'LastFilename', title: __('LastFilename'), operate: 'LIKE'},
                        // {field: 'preNo', title: __('PreNo'), operate: 'LIKE'},
                        // {field: 'gno', title: __('Gno'), operate: 'LIKE'},
                        {field: 'unit', title: __('Unit'), operate: 'LIKE'},
                        {field: 'qty1', title: __('Qty1'), operate:'BETWEEN'},
                        {field: 'unit1', title: __('Unit1'), operate: 'LIKE'},
                        {field: 'qty2', title: __('Qty2'), operate:'BETWEEN'},
                        {field: 'unit2', title: __('Unit2'), operate: 'LIKE'},
                        {field: 'weight', title: __('Weight'), operate:'BETWEEN'},
                        {field: 'netWeight', title: __('NetWeight'), operate:'BETWEEN'},
                        // {field: 'disabled', title: __('Disabled'), searchList: {"0":__('Disabled 0'),"1":__('Disabled 1')}, formatter: Table.api.formatter.normal},
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
