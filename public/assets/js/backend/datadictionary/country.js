define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datadictionary/country/index' + location.search,
                    add_url: 'datadictionary/country/add',
                    edit_url: 'datadictionary/country/edit',
                    del_url: 'datadictionary/country/del',
                    multi_url: 'datadictionary/country/multi',
                    import_url: 'datadictionary/country/import',
                    table: 'dic_country',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'couID',
                sortName: 'couID',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'couID', title: __('CouID')},
                        {field: 'CName', title: __('CName'), operate: 'LIKE'},
                        {field: 'EName', title: __('EName'), operate: 'LIKE'},
                        {field: 'continent', title: __('Continent'), operate: 'LIKE'},
                        {field: 'customsCode', title: __('CustomsCode'), operate: 'LIKE'},
                        {field: 'countryCode', title: __('CountryCode'), operate: 'LIKE'},
                        {field: 'desPort', title: __('DesPort'), operate: 'LIKE'},
                        {field: 'logCode', title: __('LogCode'), operate: 'LIKE'},
                        {field: 'ShopCode', title: __('ShopCode'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
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
