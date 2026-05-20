define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'excbe/index' + location.search,
                    // add_url: 'excbe/add',
                    edit_url: 'excbe/edit',
                    del_url: 'excbe/del',
                    multi_url: 'excbe/multi',
                    import_url: 'excbe/import',
                    table: 'excbe',
                }
            });



            var table = $("#table");
            table.on('load-success.bs.table', function (e, data) {
                //这里我们手动设置底部的值
                $("#count").text(data.extend.count);
                $("#counts").text(data.extend.counts);
                $("#countlogis").text(data.extend.countlogis);

            });

            // 初始化表格
            table.bootstrapTable({
                pageSize: 10,
                pageList: [10, 25, 50, 100,200,500,'All'],
                searchFormVisible: true,
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'orderNo', title: __('Orderno'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('报关状态'),custom:{'0':'yellow','3':'purple'},
                            searchList: {"-1": '数据有误', "0": '待报关',"1": '三单完成', "2": '603完成',"3": '607总分单完成',"4": '507运抵完成',"5": '509离境完成',"6":'605撤单完成',"7":'701完成'}, formatter: Table.api.formatter.status},
                        {field: 'final_amount', title: __('总支付价'),operate: false},
                        {field: 'item_currency', title: __('Item_currency'),operate: false},
                        {field: 'weights', title: __('总毛重'),operate: false},
                        {field: 'netWeights', title: __('总净重'),operate: false},
                        // {field: 'totalPackageNo', title: __('Totalpackageno'),operate: false},
                        {field: 'logisticsNo', title: __('Logisticsno'), operate: 'LIKE'},
                        {field: 'billNo', title: __('Billno'), operate: '='},
                        {field: 'invtNo', title: __('清单号'), operate: 'LIKE'},
                        {field: 'country', title: __('Country'),operate: false},
                        {field: 'logisticsCode', title: __('Logisticscode'), operate: 'LIKE'},
                        {field: 'weight', title: __('Weight'), operate:'BETWEEN'},
                      //  {field: 'product_bn', title: __('Product_bn'), operate: 'LIKE'},
                        {field: 'product_name', title: __('Product_name')},
                        {field: 'gcode', title: __('Gcode'),operate: false},
                        {field: 'qty', title: __('Qty'),operate: false},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'netWeight', title: __('Netweight'), operate:'BETWEEN'},
                        {field: 'voyageNo', title: __('Voyageno'),operate: false},
                        {field: 'cost_freight', title: __('Cost_freight'),operate: false},
                        {field: 'freight_currency', title: __('Freight_currency'),operate: false},
                        {field: 'ebpCode', title: __('电商平台代码 '),operate: false},
                        {field: 'ebpName', title: __('电商平台名称 '),operate: false},
                        {field: 'ebcCode', title: __('电商企业代码 '),operate: false},
                        {field: 'ebcName', title: __('电商企业名称 '),operate: false},
                        {field: 'logisticsCode', title: __('物流企业代码 '),operate: false},
                        {field: 'logisticsName', title: __('物流企业名称 '),operate: false},
                        {field: 'customsCode', title: __('申报海关代码 '),operate: false},
                        {field: 'portCode', title: __('口岸海关代码 '),operate: false},
                        {field: 'statisticsFlag', title: __('申报业务类型 '),operate: false},
                        {field: 'agentCode', title: __('电商平台代码 '),operate: false},
                        {field: 'agentName', title: __('申报企业名称 '),operate: false},
                        {field: 'ownerCode', title: __('生产销售企业代码 '),operate: false},
                        {field: 'ownerName', title: __('生产销售企业名称 '),operate: false},
                        {field: 'tradeMode', title: __('贸易方式 '),operate: false},
                        {field: 'trafMode', title: __('运输方式 '),operate: false},
                        
                       
                        
                        // {field: 'order_status', title: __('303回写状态'),operate: false},
                        // {field: 'receipt_status', title: __('403回写状态'),operate: false},
                        // {field: 'waybill_status', title: __('505回写状态'),operate: false},
                        {field: 'inventory_status', title: __('603回写状态')},
                        // {field: 'inventory_returnInfo', title: __('603回写内容'),operate: false},
                        // {field: 'total_score_sheet_status', title: __('607回写状态'),operate: false},
                        // {field: 'arrive_status', title: __('507回写状态'),operate: false},
                        // {field: 'leave_status', title: __('609回写状态'),operate: false},
                        // {field: 'cancel_status', title: __('605回写状态'),operate: false},
                        {field: 'gmodel', title: __('Gmodel') ,operate: false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });


            //打印面单

            $(".btn-letian").click(function(){
                var index = layer.load();
                var ids = Table.api.selectedids(table);
                var billNo  = Table.api.getrowdata(table, 0);
                console.log(billNo.billNo);
                layer.confirm('确定去打印面单?', {closeBtn: 0,title: "操作提示",btn: ['是','否'] },
                    function(index){

                        window.open("excbe/letian?billNo=" + billNo.billNo);
                        layer.closeAll('loading');

                    },
                    function(index){
                        layer.closeAll('loading');
                    }
                );
            });
            $(".btn-letians").click(function(){
                var index = layer.load();
                var ids = Table.api.selectedids(table);
                var billNo  = Table.api.getrowdata(table, 0);

                layer.confirm('确定去打印面单?', {closeBtn: 0,title: "操作提示",btn: ['是','否'] },
                    function(index){

                        window.open("excbe/letians?ids=" + ids);
                        layer.closeAll('loading');

                    },
                    function(index){
                        layer.closeAll('loading');
                    }
                );
            });
            //三单
            $(".btn-customsOne").click(function(){
                var index = layer.load();
                var ids = Table.api.selectedids(table);
                var billNo  = Table.api.getrowdata(table, 0);
                console.log(billNo.billNo);
                // layer.confirm('确定三单申报?', {btn: ['是','否'] },
                layer.confirm('请选择三单申报选项！', {
                    title: "操作提示",
                    maxWidth: 10,
                    btnAlign: "l",
                    closeBtn: 1,
                    resize: true,
                    icon:3,
                    btn: ['选择：', '303申报~', '403申报~', '505申报~', '全部申报~'], cancel: function () {
                          layer.closeAll('loading');
                    }
                    , btn4: function (index, layero) {
                            $.post("excbe/customsOne", {ids: ids,billNo:billNo.billNo,status: '505'}, function (response) {
                                if (response.code == 1) {
                                    layer.closeAll();
                                    Toastr.success(response.msg);
                                    $(".btn-refresh").trigger('click');
                                } else {
                                   layer.closeAll('loading');
                                   Toastr.error(response.msg);

                                }
                            }, 'json');
                    }
                    , btn3: function (index, layero) {
                        $.post("excbe/customsOne", {ids: ids,billNo:billNo.billNo, status: '403'}, function (response) {
                            if (response.code == 1) {
                                layer.closeAll('loading');
                                Toastr.success(response.msg);
                                $(".btn-refresh").trigger('click');

                            } else {
                                layer.closeAll('loading');
                                Toastr.error(response.msg);
                            }
                        }, 'json');
                    },
                    btn5:function(index, layero) {
                        $.post("excbe/customsOne", {ids: ids,billNo:billNo.billNo, status: 'all'}, function (response) {
                            if (response.code == 1) {
                                layer.closeAll();
                                Toastr.success(response.msg);
                                 $(".btn-refresh").trigger('click');
                            } else {
                                layer.closeAll('loading');
                                Toastr.error(response.msg);
                            }
                        }, 'json');
                    },
                    btn1:function(index, layero) {
                        layer.closeAll('loading');
                    },
                    btn2:function(index) {
                        $.post("excbe/customsOne", {ids: ids,billNo:billNo.billNo, status: '303'}, function (response) {
                            if (response.code == 1) {
                                layer.closeAll('loading');
                                Toastr.success(response.msg);
                                $(".btn-refresh").trigger('click');
                            } else {
                                layer.closeAll('loading');
                                Toastr.error(response.msg);
                            }
                        }, 'json');
                     }
                    });
                });


            //清单
            $(".btn-customsTwo").click(function(){
                var index = layer.load();
                var ids = Table.api.selectedids(table);
                var billNo  = Table.api.getrowdata(table, 0);
                console.log(billNo.billNo);
                layer.confirm('确定603清单申报?', {closeBtn: 0,title: "操作提示", btn: ['是','否'] },
                    function(index){
                        layer.close(index);
                        $.post("excbe/customsTwo", {ids:ids,billNo:billNo.billNo},function(response){
                            if(response.code == 1){
                                layer.closeAll();
                                Toastr.success(response.msg)
                                $(".btn-refresh").trigger('click');
                            }else{
                                // window.parent.location.reload();
                                layer.closeAll('loading');
                                Toastr.error(response.msg)
                            }
                        }, 'json')
                    },
                    function(index){
                        layer.closeAll('loading');
                    }
                );
            });

            //总分单
            $(".btn-customsThree").click(function(){
                var index = layer.load();
                var ids = Table.api.selectedids(table);
                var billNo  = Table.api.getrowdata(table, 0);
                console.log(billNo.billNo);
                layer.confirm('确定607总分单申报?', {closeBtn: 0,title: "操作提示",btn: ['是','否'] },
                    function(index){
                        layer.close(index);
                        $.post("excbe/customsThree", {ids:ids,billNo:billNo.billNo},function(response){
                            if(response.code == 1){
                                layer.closeAll();
                                Toastr.success(response.msg)
                                 $(".btn-refresh").trigger('click');
                            }else{
                                layer.closeAll('loading');
                                Toastr.error(response.msg)
                            }
                        }, 'json')
                    },
                    function(index){
                        layer.closeAll('loading');
                    }
                );
            });

            //运抵
            $(".btn-customsFour").click(function(){
                var ids = Table.api.selectedids(table);
                var billNo  = Table.api.getrowdata(table, 0);
                console.log(billNo.billNo);
                layer.confirm('确定507运抵申报?', {closeBtn: 0,title: "操作提示",btn: ['是','否'] },
                    function(index){
                        layer.close(index);
                        $.post("excbe/customsFour", {ids:ids,billNo:billNo.billNo},function(response){
                            if(response.code == 1){
                                layer.closeAll();
                                Toastr.success(response.msg)
                                 $(".btn-refresh").trigger('click');
                            }else{
                                layer.closeAll('loading');
                                Toastr.error(response.msg)
                            }
                        }, 'json')
                    },
                    function(index){
                        layer.closeAll('loading');
                    }
                );
            });

            //离境
            $(".btn-customsFive").click(function(){
                var ids = Table.api.selectedids(table);
                var billNo  = Table.api.getrowdata(table, 0);
                console.log(billNo.billNo);
                layer.confirm('确定509离境申报?', {closeBtn: 0,title: "操作提示",btn: ['是','否'] },
                    function(index){
                        layer.close(index);
                        $.post("excbe/customsFive", {ids:ids,billNo:billNo.billNo},function(response){
                            if(response.code == 1){
                                layer.closeAll('loading');
                                Toastr.success(response.msg)
                                $(".btn-refresh").trigger('click');
                            }else{
                                layer.closeAll('loading');
                                Toastr.error(response.msg)
                            }
                        }, 'json')
                    },
                    function(index){
                        layer.closeAll('loading');
                    }
                );
            });
            
            
             //汇总
            $(".btn-summary").click(function(){
                var ids = Table.api.selectedids(table);
                var billNo  = Table.api.getrowdata(table, 0);
                console.log(billNo.billNo);
                layer.confirm('确定701汇总申报?', {closeBtn: 0,title: "操作提示",btn: ['是','否'] },
                    function(index){
                        layer.close(index);
                        $.post("excbe/summary", {ids:ids,billNo:billNo.billNo},function(response){
                            if(response.code == 1){
                                layer.closeAll('loading');
                                Toastr.success(response.msg)
                                $(".btn-refresh").trigger('click');
                            }else{
                                layer.closeAll('loading');
                                Toastr.error(response.msg)
                            }
                        }, 'json')
                    },
                    function(index){
                        layer.closeAll('loading');
                    }
                );
            });

             //订单撤销
            $(".btn-customsEight").click(function(){
                var ids = Table.api.selectedids(table);
                var billNo  = Table.api.getrowdata(table, 0);
                console.log(billNo.billNo);
                layer.confirm('确定订单撤销申报?', {closeBtn: 0,title: "操作提示",btn: ['是','否'] },
                function(index){
                    layer.close(index);
                        $.post("excbe/customsEight", {ids:ids,billNo:billNo.billNo},function(response){
                            if(response.code == 1){
                                layer.closeAll('loading');
                                Toastr.success(response.msg)
                                 $(".btn-refresh").trigger('click');
                            }else{
                                layer.closeAll('loading');
                                Toastr.error(response.msg)
                            }
                        }, 'json')
                    },
                    function(index){
                        // window.parent.location.reload(); //刷新页面
                        layer.closeAll('loading');
                    }
                );
            });


            //订单删除
            $(".btn-dels").click(function(){
                var ids = Table.api.selectedids(table);
                var billNo  = Table.api.getrowdata(table, 0);
                var all = table.bootstrapTable('getOptions').totalRows;
                layer.confirm("确定删除"+ billNo.billNo +"提运单"+ all +"条数据?", {closeBtn: 0,title: "操作提示",btn: ['是','否'] },
                    function(index){
                        layer.close(index);
                        $.post("excbe/dels", {ids:ids,billNo:billNo.billNo},function(response){
                            if(response.code == 1){
                                layer.closeAll('loading');
                                Toastr.success(response.msg)
                                $(".btn-refresh").trigger('click');
                            }else{
                                layer.closeAll('loading');
                                Toastr.error(response.msg)
                            }
                        }, 'json')
                    },
                    function(index){
                        // window.parent.location.reload(); //刷新页面
                        layer.closeAll('loading');
                    }
                );
            });

            //导出
            var submitForm = function (ids, layero) {
                var options = table.bootstrapTable('getOptions');
                console.log(options);
                var columns = [];
                $.each(options.columns[0], function (i, j) {
                    if (j.field && !j.checkbox && j.visible && j.field != 'operate') {
                        columns.push(j.field);
                    }
                });
                var search = options.queryParams({});
                $("input[name=search]", layero).val(options.searchText);
                $("input[name=ids]", layero).val(ids);
                $("input[name=filter]", layero).val(search.filter);
                $("input[name=op]", layero).val(search.op);
                $("input[name=columns]", layero).val(columns.join(','));
                $("form", layero).submit();
            };
            //导出汇总
            $(document).on("click", ".btn-export", function () {
                var ids = Table.api.selectedids(table);
                var page = table.bootstrapTable('getData');
                var all = table.bootstrapTable('getOptions').totalRows;
                console.log(ids, page, all);
                Layer.confirm("请选择导出的选项<form action='" + Fast.api.fixurl("excbe/export") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'><input type='hidden' name='columns'></form>", {
                    title: '导出数据',
                    btn: ["选中项(" + ids.length + "条)", "本页(" + page.length + "条)",
                        "全部(" + all + "条)"],
                    //btn: ["选中项(" + ids.length + "条)"],
                    success: function (layero, index) {
                        $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                    },
                    yes: function (index, layero) {
                        submitForm(ids.join(","), layero);
                        return false;
                    },
                    btn2: function (index, layero) {
                        var ids = [];
                        $.each(page, function (i, j) {
                            ids.push(j.id);
                        });
                        submitForm(ids.join(","), layero);
                        return false;
                    },
                    btn3: function (index, layero) {
                        submitForm("all", layero);
                        return false;
                    }
                });
            });
        //导出数据
            $(document).on("click", ".btn-exports", function () {
                var ids = Table.api.selectedids(table);
                var page = table.bootstrapTable('getData');
                var all = table.bootstrapTable('getOptions').totalRows;
                console.log(ids, page, all);
                Layer.confirm("请选择导出的选项<form action='" + Fast.api.fixurl("excbe/exports") + "' method='post' target='_blank'><input type='hidden' name='ids' value='' /><input type='hidden' name='filter' ><input type='hidden' name='op'><input type='hidden' name='search'><input type='hidden' name='columns'></form>", {
                    title: '导出数据',
                    btn: ["选中项(" + ids.length + "条)", "本页(" + page.length + "条)",
                        "全部(" + all + "条)"],
                    //btn: ["选中项(" + ids.length + "条)"],
                    success: function (layero, index) {
                        $(".layui-layer-btn a", layero).addClass("layui-layer-btn0");
                    },
                    yes: function (index, layero) {
                        submitForm(ids.join(","), layero);
                        return false;
                    },
                    btn2: function (index, layero) {
                        var ids = [];
                        $.each(page, function (i, j) {
                            ids.push(j.id);
                        });
                        submitForm(ids.join(","), layero);
                        return false;
                    },
                    btn3: function (index, layero) {
                        submitForm("all", layero);
                        return false;
                    }
                });
            });


            // 绑定TAB事件
            $('.panel-heading a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var field = $(this).closest("ul").data("field");
                var value = $(this).data("value");
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    var filter = {};
                    if (value !== '') {
                        filter[field] = value;
                    }
                    params.filter = JSON.stringify(filter);
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
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
