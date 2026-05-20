define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'eow/eoworderlist/index' + location.search,
                    add_url: 'eow/eoworderlist/add',
                    edit_url: 'eow/eoworderlist/edit',
                    del_url: 'eow/eoworderlist/del',
                    multi_url: 'eow/eoworderlist/multi',
                    import_url: 'eow/eoworderlist/import',
                    table: 'exorder',
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
                        {field: 'order_number', title: __('Order_number'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'cop_no', title: __('Cop_no'), operate: 'LIKE'},
                        {field: 'customs_code', title: __('Customs_code'), operate: 'LIKE'},
                        {field: 'port_code', title: __('Port_code'), operate: 'LIKE'},
                        {field: 'cut_mode', title: __('Cut_mode'), operate: 'LIKE'},
                        {field: 'distinate_port', title: __('Distinate_port'), operate: 'LIKE'},
                        {field: 'edistinate_port', title: __('Edistinate_port'), operate: 'LIKE'},
                        {field: 'overseas_consignee_ename', title: __('Overseas_consignee_ename'), operate: 'LIKE'},
                        {field: 'desp_port_code', title: __('Desp_port_code'), operate: 'LIKE'},
                        {field: 'trans_mode', title: __('Trans_mode'), operate: 'LIKE'},
                        {field: 'traf_mode', title: __('Traf_mode'), operate: 'LIKE'},
                        {field: 'pack_no', title: __('Pack_no'), operate: 'LIKE'},
                        {field: 'wrap_type', title: __('Wrap_type'), operate: 'LIKE'},
                        {field: 'contr_no', title: __('Contr_no'), operate: 'LIKE'},
                        {field: 'client_seq_no', title: __('Client_seq_no'), operate: 'LIKE'},
                        {field: 'notes', title: __('Notes'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'seq_no', title: __('Seq_no'), operate: 'LIKE'},
                        {field: 'entery_id', title: __('Entery_id'), operate: 'LIKE'},
                        {field: 'agent_name', title: __('Agent_name'), operate: 'LIKE'},
                        {field: 'response_code', title: __('Response_code'), operate: 'LIKE'},
                        {field: 'channel', title: __('Channel'), operate: 'LIKE'},
                        {field: 'consignee_country', title: __('Consignee_country'), operate: 'LIKE'},
                        {field: 'country_cname', title: __('Country_cname'), operate: 'LIKE'},
                        {field: 'country_code', title: __('Country_code'), operate: 'LIKE'},
                        {field: 'ebc_id', title: __('Ebc_id')},
                        {field: 'ebc_code', title: __('Ebc_code'), operate: 'LIKE'},
                        {field: 'ebc_name', title: __('Ebc_name'), operate: 'LIKE'},
                        {field: 'trade_scc', title: __('Trade_scc'), operate: 'LIKE'},
                        {field: 'bill_no', title: __('Bill_no'), operate: 'LIKE'},
                        {field: 'voyage_no', title: __('Voyage_no'), operate: 'LIKE'},
                        {field: 'ebp_id', title: __('Ebp_id')},
                        {field: 'ebp_code', title: __('Ebp_code'), operate: 'LIKE'},
                        {field: 'ebp_name', title: __('Ebp_name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'agent_id', title: __('Agent_id')},
                        {field: 'agent_code', title: __('Agent_code'), operate: 'LIKE'},
                        {field: 'agent_scc', title: __('Agent_scc'), operate: 'LIKE'},
                        {field: 'consignee_country_name', title: __('Consignee_country_name'), operate: 'LIKE'},
                        {field: 'consignee_address', title: __('Consignee_address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'cost_item', title: __('Cost_item'), operate:'BETWEEN'},
                        {field: 'freight', title: __('Freight'), operate:'BETWEEN'},
                        {field: 'freight_currency', title: __('Freight_currency'), operate: 'LIKE'},
                        {field: 'fee_mark', title: __('Fee_mark'), operate: 'LIKE'},
                        {field: 'cost_freight', title: __('Cost_freight'), operate:'BETWEEN'},
                        {field: 'insur_curr', title: __('Insur_curr'), operate: 'LIKE'},
                        {field: 'insur_mark', title: __('Insur_mark'), operate: 'LIKE'},
                        {field: 'insur_rate', title: __('Insur_rate'), operate:'BETWEEN'},
                        {field: 'other_curr', title: __('Other_curr'), operate: 'LIKE'},
                        {field: 'other_mark', title: __('Other_mark'), operate: 'LIKE'},
                        {field: 'other_rate', title: __('Other_rate'), operate:'BETWEEN'},
                        {field: 'final_amount', title: __('Final_amount'), operate:'BETWEEN'},
                        {field: 'note', title: __('Note'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'weight', title: __('Weight'), operate:'BETWEEN'},
                        {field: 'net_weight', title: __('Net_weight'), operate:'BETWEEN'},
                        {field: 'member_id', title: __('Member_id')},
                        {field: 'member_name', title: __('Member_name'), operate: 'LIKE'},
                        {field: 'confirm', title: __('Confirm'), searchList: {"draft":__('Draft'),"ok":__('Ok'),"cancel":__('Cancel')}, formatter: Table.api.formatter.normal},
                        {field: 'order_type', title: __('Order_type'), searchList: {"E":__('E'),"B":__('B'),"W":__('W')}, formatter: Table.api.formatter.normal},
                        {field: 'declare_status', title: __('Declare_status'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        {field: 'import_error', title: __('Import_error'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'owner_code', title: __('Owner_code'), operate: 'LIKE'},
                        {field: 'owner_name', title: __('Owner_name'), operate: 'LIKE'},
                        {field: 'owner_scc', title: __('Owner_scc'), operate: 'LIKE'},
                        {field: 'order_status', title: __('Order_status'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        {field: 'order_updateTime', title: __('Order_updateTime'), operate: 'LIKE'},
                        {field: 'order_returnInfo', title: __('Order_returnInfo'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'inventory_status', title: __('Inventory_status'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        {field: 'inventory_updateTime', title: __('Inventory_updateTime'), operate: 'LIKE'},
                        {field: 'inventory_returnInfo', title: __('Inventory_returnInfo'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'auth_id', title: __('Auth_id')},
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
                url: 'eow/eoworderlist/recyclebin' + location.search,
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
                                    url: 'eow/eoworderlist/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'eow/eoworderlist/destroy',
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
