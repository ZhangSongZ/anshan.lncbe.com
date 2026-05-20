define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'census/index' + location.search,
                    add_url: 'census/add',
                    edit_url: 'census/edit',
                    del_url: 'census/del',
                    multi_url: 'census/multi',
                    import_url: 'census/import',
                    table: 'census',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id',title: __('序号'), formatter:function (value,row,index)
                            {
                                return index+1;
                            }
                        },
                        {field: 'billNo', title: __('Billno'), operate: 'LIKE'},
                        {field: 'countpack', title: __('Countpack'), operate: 'LIKE'},
                        {field: 'countlogis', title: __('Countlogis'), operate: 'LIKE'},
                        {field: 'allmoeny', title: __('Allmoeny'), operate: 'LIKE'},
                        {field: 'allWeight', title: __('Allweight'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime,sortable:true},
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
