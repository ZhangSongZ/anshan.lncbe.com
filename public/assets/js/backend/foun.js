define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({

                extend: {
                    index_url: 'foun/index' + location.search,
                    add_url: 'foun/add',
                    edit_url: 'foun/edit',
                    del_url: 'foun/del',
                    multi_url: 'foun/multi',
                    import_url: 'foun/import',
                    table: 'foun',
                }
            });
            var table = $("#table");

            Form.api.bindevent("form");

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
