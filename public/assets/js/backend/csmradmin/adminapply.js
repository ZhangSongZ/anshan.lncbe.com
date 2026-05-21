define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置333
            Table.api.init({
                extend: {
                    index_url: 'csmradmin/adminapply/index' + location.search,
                    add_url: 'csmradmin/adminapply/add',
                    edit_url: 'csmradmin/adminapply/edit',
                    del_url: 'csmradmin/adminapply/del',
                    multi_url: 'csmradmin/adminapply/multi',
                    table: 'csmradmin_adminapply',
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
                        {field: 'id', title: __('Id')},
                        {field: 'username', title: __('Username')},
                        {field: 'nickname', title: __('Nickname')},
                        // {field: 'password', title: __('Password')},
                        // {field: 'salt', title: __('Salt')},
                        // {field: 'avatar', title: __('Avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'email', title: __('Email')},
                        {field: 'ip', title: __('Ip')},
                        {field: 'phone', title: __('联系电话')},
                        {field: 'type', title: __('企业类型')},
                        // {field: 'relate_admin_id', title: __('Relate_admin_id')},
                        // {field: 'auth_group_ids', title: __('Auth_group_ids')},
                        {field: 'auditstatus', title: __('Auditstatus'), searchList: {"-2":__('Auditstatus -2'),"-1":__('Auditstatus -1'),"0":__('Auditstatus 0'),"1":__('Auditstatus 1')}, formatter: Table.api.formatter.status},
                        {field: 'auditreturn', title: __('Auditreturn')},
                        // {field: 'audituser_id', title: __('Audituser_id')},
                        {field: 'audituser', title: __('Audituser')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('审核时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
//                        {field: 'b1', title: __('B1')},
//                        {field: 'b2', title: __('B2')},
//                        {field: 'b3', title: __('B3')},
//                        {field: 'b4', title: __('B4')},
//                        {field: 'b5', title: __('B5')},
//                        {field: 'b6', title: __('B6')},
//                        {field: 'b7', title: __('B7')},
//                        {field: 'b8', title: __('B8')},
//                        {field: 'b9', title: __('B9')},
                        {
                        	field: 'operate', 
                        	title: __('Operate'), 
                        	table: table, events: Table.api.events.operate, 
                        	//formatter: Table.api.formatter.operate,
                        	formatter: Table.api.formatter.buttons,
                            buttons: [
                                {
                                    name: 'edit',
                                    text: __('补充信息'),
                                    classname: 'btn btn-xs btn-warning btn-dialog',
                                    icon: 'fa fa-pencil',
                                    url: 'csmradmin/adminapply/edit',
                                    callback: function (data) {
                                        Fast.api.close(data);
                                    },
                                    visible: function (row) {
                                        if(table.data("operate-edit")){
                                            return true;
                                        }else{
                                            return false;
                                        }    
                                    }                                    
                                },
                                {
                                    name: 'submitauditreturn',
                                    text: __('审核退回1'),
                                    classname: 'btn btn-xs btn-danger btn-dialog',
                                    icon: 'fa fa-file',
                                    url: 'csmradmin/adminapply/submitauditreturn',
                                    callback: function (data) {
                                        alert('callbak');
                                        Fast.api.close(data);
                                    },
                                    visible: function (row) {
                                        if(row.auditstatus=='0' && table.data("submitauditreturn")){
                                        	return true;
                                        }else{
                                        	return false;
                                        }    
                                    }                                    
                                },

                                {
                                    name: 'submitauditok',
                                    text: __('审核通过'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    icon: 'fa fa-file',
                                    url: 'csmradmin/adminapply/submitauditok',
                                    callback: function (data) {
                                        Fast.api.close(data);
                                    },
                                    visible: function (row) {
                                        if((row.auditstatus=='0'||row.auditstatus=='-1') && table.data("submitauditok")){
                                        	return true;
                                        }else{
                                        	return false;
                                        }    
                                    }                                    
                                },
                                {
                                    name: 'view',
                                    text: __('查看'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-file',
                                    url: 'csmradmin/adminapply/view',
                                    callback: function (data) {
                                        Fast.api.close(data);
                                    },
                                    visible: function (row) {
                                        if(table.data("view")){
                                            return true;
                                        }else{
                                            return false;
                                        }    
                                    }                                    
                                },                                
                             ]
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
        submitauditreturn:function(){
            Controller.api.bindevent();
        },
        submitauditok:function(){
            Controller.api.bindevent();
        },  
        view:function(){
        },               
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});