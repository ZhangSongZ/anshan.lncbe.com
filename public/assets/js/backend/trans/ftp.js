define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'trans/ftp/index' + location.search,
                    add_url: 'trans/ftp/add',
                    edit_url: 'trans/ftp/edit',
                    del_url: 'trans/ftp/del',
                    multi_url: 'trans/ftp/multi',
                    import_url: 'trans/ftp/import',
                    table: 'ftp',
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
                        {field: 'ftp_name', title: __('Ftp_name'), operate: 'LIKE'},
                        {field: 'ftp_ip', title: __('Ftp_ip'), operate: 'LIKE'},
                        {field: 'ftp_username', title: __('Ftp_username'), operate: 'LIKE'},
                        {field: 'ftp_password', title: __('Ftp_password'), operate: 'LIKE'},
                        {field: 'ftp_port', title: __('Ftp_port'), operate: 'LIKE'},
                        {field: 'ftp_ssl', title: __('Ftp_ssl'), operate: 'LIKE'},
                        {field: 'ftp_pasv', title: __('Ftp_pasv'), operate: 'LIKE'},
                        {field: 'ftp_timeout', title: __('Ftp_timeout')},
                        {field: 'ftp_dir', title: __('Ftp_dir'), operate: 'LIKE'},
                        {field: 'ftp_note', title: __('Ftp_note'), operate: 'LIKE'},
                        {field: 'dxpId', title: __('DxpId'), operate: 'LIKE'},
                        {field: 'switch', title: __('Switch'), table: table, formatter: Table.api.formatter.toggle},
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
