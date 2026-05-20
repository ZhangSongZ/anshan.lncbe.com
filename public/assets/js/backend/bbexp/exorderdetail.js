define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bbexp/exorderdetail/index' + location.search,
                    add_url: 'bbexp/exorderdetail/add',
                    edit_url: 'bbexp/exorderdetail/edit',
                    del_url: 'bbexp/exorderdetail/del',
                    multi_url: 'bbexp/exorderdetail/multi',
                    import_url: 'bbexp/exorderdetail/import',
                    table: 'exorderdetail',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
				searchFormVisible: true,
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'),operate: false},
                        {field: 'order_number', title: __('Order_number'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'member_id', title: __('Member_id')},
                        // {field: 'member_name', title: __('Member_name'), operate: 'LIKE'},
                        {field: 'product_bn', title: __('Product_bn'), operate: 'LIKE'},
                        {field: 'product_name', title: __('Product_name'), operate: 'LIKE'},
                        {field: 'gcode', title: __('Gcode'), operate: 'LIKE'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN',operate: false},
                        {field: 'qty', title: __('Qty'), operate:'BETWEEN',operate: false},
                        {field: 'total', title: __('Total'), operate:'BETWEEN',operate: false},
                        {field: 'unit', title: __('Unit'), operate: 'LIKE',operate: false},
                        {field: 'unit1', title: __('Unit1'), operate: 'LIKE',operate: false},
                        {field: 'unit2', title: __('Unit2'), operate: 'LIKE',operate: false},
                        {field: 'qty1', title: __('Qty1'), operate:'BETWEEN',operate: false},
                        {field: 'qty2', title: __('Qty2'), operate:'BETWEEN',operate: false},
                        {field: 'destination_country', title: __('Destination_country'), operate: 'LIKE'},
                        {field: 'origin_country', title: __('Origin_country'), operate: 'LIKE'},
                        {field: 'district_code', title: __('District_code'), operate: 'LIKE'},
                        {field: 'duty_mode', title: __('Duty_mode'), operate: 'LIKE',operate: false},
                        {field: 'gmodel', title: __('Gmodel'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content,operate: false},
                        {field: 'item_currency', title: __('Item_currency'),operate: false},
                        // {field: 'auth_id', title: __('Auth_id')},
                        // {field: 'is_deleted', title: __('Is_deleted')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'bbexp/exorderdetail/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'order_number', title: __('Order_number')},
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '140px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'bbexp/exorderdetail/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'bbexp/exorderdetail/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
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
