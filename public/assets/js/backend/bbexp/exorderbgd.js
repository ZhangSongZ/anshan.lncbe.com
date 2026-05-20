define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'bbexp/exorderbgd/index' + location.search,
                    add_url: 'bbexp/exorderbgd/add',
                    edit_url: 'bbexp/exorderbgd/edit',
                    del_url: 'bbexp/exorderbgd/del',
                    multi_url: 'bbexp/exorderbgd/multi',
                    import_url: 'bbexp/exorderbgd/import',
                    table: 'exorder',
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
                        {field: 'customs_code', title: __('Customs_code'), operate: 'LIKE'},
                        {field: 'port_code', title: __('Port_code'), operate: 'LIKE'},
                        {field: 'seq_no', title: __('Seq_no'), operate: 'LIKE'},
                        {field: 'entery_id', title: __('Entery_id'), operate: 'LIKE'},
                        {field: 'channel', title: __('Channel'), operate: 'LIKE'},
                        {field: 'country_cname', title: __('Country_cname'), operate: 'LIKE'},
                        {field: 'ebc_name', title: __('Ebc_name'), operate: 'LIKE'},
                        {field: 'bill_no', title: __('Bill_no'), operate: 'LIKE'},
                        // {field: 'voyage_no', title: __('Voyage_no'), operate: 'LIKE'},
                        {field: 'traf_name', title: __('运输工具名称'), operate: 'LIKE',operate: false},
                        {field: 'voy_no', title: __('航次号'), operate: 'LIKE',operate: false},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                url: 'bbexp/exorderbgd/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
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
                                    url: 'bbexp/exorderbgd/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'bbexp/exorderbgd/destroy',
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
