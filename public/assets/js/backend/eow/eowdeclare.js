define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'eow/eowdeclare/index' + location.search,
                    add_url: 'eow/eowdeclare/add',
                    edit_url: 'eow/eowdeclare/edit',
                    del_url: 'eow/eowdeclare/del',
                    multi_url: 'eow/eowdeclare/multi',
                    import_url: 'eow/eowdeclare/import',
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
                        {field: 'order_number', title: __('Order_number'), operate: 'LIKE',
									formatter: function (value, row, index) {
										// 为订单号创建一个带有 btn-dialog 类的按钮
										// url 中的 {id} 会被替换为当前行的实际 ID
										return '<a href="javascript:;" class="btn-dialog" ' +
											   'data-title="订单详情 - ' + value + '" ' +
											   'data-url="bbexp/exorder/details?id=' + row.id + '&order_number=' + row.order_number + '">' + 
											   value + '</a>';
									}
						},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'client_seq_no', title: __('Client_seq_no'), operate: 'LIKE'},
                        {field: 'seq_no', title: __('Seq_no'), operate: 'LIKE'},
                        {field: 'entery_id', title: __('Entery_id'), operate: 'LIKE'},
                        {field: 'bill_no', title: __('Bill_no'), operate: 'LIKE'},
                        {field: 'ebc_name', title: __('Ebc_name'), operate: 'LIKE'},
                        {field: 'agent_name', title: __('Agent_name'), operate: 'LIKE'},
                        {field: 'order_status', title: __('Order_status'), 
						searchList: {"0":__('未申报'),"1":__('已申报'),"2":__('新增申报成功')},
						formatter: Table.api.formatter.status},
                        {field: 'response_code', title: __('报关单响应'), 
						searchList: {"0":__('暂存成功'),"1":__('暂存失败')},
						},
                        {field: 'channel', title: __('Channel'), 
						searchList: {"0":__('未申报'),"7":__('申报成功'),"L":__('入库成功'),"L":__('海关接受申报'),"P":__('放行'),"R":__('结关')},
						formatter: Table.api.formatter.status},
                        {field: 'customs_code', title: __('Customs_code'), operate: 'LIKE',operate: false},
                        {field: 'port_code', title: __('Port_code'), operate: 'LIKE',operate: false},
                        {field: 'cut_mode', title: __('Cut_mode'), operate: 'LIKE',operate: false},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime,operate: false},
                        //{field: 'cop_no', title: __('Cop_no'), operate: 'LIKE'},
                        {field: 'distinate_port', title: __('Distinate_port'), operate: 'LIKE',operate: false},
                        {field: 'edistinate_port', title: __('Edistinate_port'), operate: 'LIKE',operate: false},
                        {field: 'overseas_consignee_ename', title: __('Overseas_consignee_ename'), operate: 'LIKE',operate: false},
                        {field: 'desp_port_code', title: __('Desp_port_code'), operate: 'LIKE',operate: false},
                        {field: 'trans_mode', title: __('Trans_mode'), operate: 'LIKE',operate: false},
                        {field: 'traf_mode', title: __('Traf_mode'), operate: 'LIKE',operate: false},
                        {field: 'pack_no', title: __('Pack_no'), operate: 'LIKE',operate: false},
                        {field: 'wrap_type', title: __('Wrap_type'), operate: 'LIKE',operate: false},
                        {field: 'notes', title: __('报关单备注'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content,operate: false},
                        {field: 'contr_no', title: __('Contr_no'), operate: 'LIKE',operate: false},
                        //{field: 'consignee_country', title: __('Consignee_country'), operate: 'LIKE'},
                        {field: 'country_cname', title: __('Country_cname'), operate: 'LIKE',operate: false},
                        {field: 'country_code', title: __('Country_code'), operate: 'LIKE',operate: false},
                        //{field: 'ebc_id', title: __('Ebc_id')},
                        {field: 'ebc_code', title: __('Ebc_code'), operate: 'LIKE',operate: false},
                        {field: 'trade_scc', title: __('Trade_scc'), operate: 'LIKE',operate: false},
                        //{field: 'voyage_no', title: __('Voyage_no'), operate: 'LIKE'},
						{field: 'traf_name', title: __('运输工具名称'), operate: 'LIKE',operate: false},
                        {field: 'voy_no', title: __('航次号'), operate: 'LIKE',operate: false},
                        //{field: 'ebp_id', title: __('Ebp_id')},
                        //{field: 'ebp_code', title: __('Ebp_code'), operate: 'LIKE'},
                        {field: 'ebp_name', title: __('Ebp_name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        //{field: 'agent_id', title: __('Agent_id')},
                        //{field: 'agent_code', title: __('Agent_code'), operate: 'LIKE'},
                        //{field: 'agent_scc', title: __('Agent_scc'), operate: 'LIKE'},
                        //{field: 'consignee_country_name', title: __('Consignee_country_name'), operate: 'LIKE'},
                        //{field: 'consignee_address', title: __('Consignee_address'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        //{field: 'cost_item', title: __('Cost_item'), operate:'BETWEEN'},
                        //{field: 'freight', title: __('Freight'), operate:'BETWEEN'},
                        {field: 'freight_currency', title: __('Freight_currency'), operate: 'LIKE',operate: false},
                        {field: 'order_type', title: __('Order_type'), searchList: {"E":__('B2C出口订单'),"B":__('B2B出口订单'),"W":__('海外仓订仓单')}, formatter: Table.api.formatter.normal,operate: false},
                        //{field: 'fee_mark', title: __('Fee_mark'), operate: 'LIKE'},
                        //{field: 'cost_freight', title: __('Cost_freight'), operate:'BETWEEN'},
                        //{field: 'insur_curr', title: __('Insur_curr'), operate: 'LIKE'},
                        //{field: 'insur_mark', title: __('Insur_mark'), operate: 'LIKE'},
                        //{field: 'insur_rate', title: __('Insur_rate'), operate:'BETWEEN'},
                        //{field: 'other_curr', title: __('Other_curr'), operate: 'LIKE'},
                        //{field: 'other_mark', title: __('Other_mark'), operate: 'LIKE'},
                        //{field: 'other_rate', title: __('Other_rate'), operate:'BETWEEN'},
                        //{field: 'final_amount', title: __('Final_amount'), operate:'BETWEEN'},
                        {field: 'note', title: __('Note'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content,operate: false},
                        {field: 'weight', title: __('Weight'), operate:'BETWEEN',operate: false},
                        {field: 'net_weight', title: __('Net_weight'), operate:'BETWEEN',operate: false},
                        //{field: 'member_id', title: __('Member_id')},
                        //{field: 'member_name', title: __('Member_name'), operate: 'LIKE'},
                        
                        //{field: 'declare_status', title: __('Declare_status'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        {field: 'import_error', title: __('Import_error'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content,operate: false},
                        //{field: 'owner_code', title: __('Owner_code'), operate: 'LIKE'},
                        {field: 'owner_name', title: __('Owner_name'), operate: 'LIKE'},
                        //{field: 'owner_scc', title: __('Owner_scc'), operate: 'LIKE'},
                        //{field: 'order_updateTime', title: __('Order_updateTime'), operate: 'LIKE'},
                        //{field: 'order_returnInfo', title: __('Order_returnInfo'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        //{field: 'inventory_status', title: __('Inventory_status'), operate: 'LIKE', formatter: Table.api.formatter.status},
                        //{field: 'inventory_updateTime', title: __('Inventory_updateTime'), operate: 'LIKE'},
                        //{field: 'inventory_returnInfo', title: __('Inventory_returnInfo'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        //{field: 'auth_id', title: __('Auth_id')},
						{field: 'confirm', title: __('Confirm'), searchList: {"draft":__('异常'),"ok":__('正常'),"cancel":__('取消')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });
             //订单申报
            $(".btn-customsOrder").click(function(){
               var ids = Table.api.selectedids(table);
			
                layer.confirm('确定订单申报?', {closeBtn: 0,title: "操作提示",btn: ['是','否'] },
                function(index){
                    layer.close(index);
				
                        $.post("eow/eowdeclare/customsOrder", {ids:ids},function(response){
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
			//报关单申报
            $(".btn-customsBgd").click(function(){
               var ids = Table.api.selectedids(table);
			
                layer.confirm('确定申报报关单?', {closeBtn: 0,title: "操作提示",btn: ['是','否'] },
                function(index){
                    layer.close(index);
				
                        $.post("eow/eowdeclare/customsBgd", {ids:ids},function(response){
						
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
                url: 'eow/eowdeclare/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
						{field: 'order_number', title: __('Order_number')},//回收站列表里增加订单号列
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
                                    url: 'eow/eowdeclare/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'eow/eowdeclare/destroy',
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
			Controller.initCodeSearch();
            Controller.api.bindevent();
        },
        edit: function () {
			Controller.initCodeSearch();
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },
		initCodeSearch: function () {
			// 监听申报单位代码
			Controller.bindAgentCodeSearch();
			// 监听EBC代码
			Controller.bindEbcCodeSearch();
			//监听生产销售单位
			Controller.bindOwnerCodeSearch();
			//国家
			Controller.bindCountrySearch();
		},
		bindAgentCodeSearch: function () {
            // 监听申报单位代码输入框的回车事件
            $('#c-agent_code').on('keypress', function(e) {
                if(e.which === 13) { // 回车键
                    e.preventDefault(); // 阻止表单提交
                    var agentCode = $(this).val().trim();
           
                    if(!agentCode) {
                        Toastr.warning("请输入申报单位代码");
                        return false;
                    }
                    // 显示加载状态
                    var $input = $(this);
                    var $submitBtn = $("form[role=form]").find("button[type=submit]");
                    $input.addClass('loading');
                    $submitBtn.prop('disabled', true);
                    
                    // 发起AJAX请求
                    $.ajax({
                        url: $(this).data('url'), // 从 data-url 获取
                        type: 'POST',
                        data: {
                            agent_code: agentCode
                        },
                        dataType: 'json',
                        success: function(response) {
                            if(response.code === 1) {
                                // 查询成功，有备案记录
                                var data = response.data;
                                // 给单位名称和社会信用代码赋值
                                $('#c-agent_name').val(data.agent_name);
                                $('#c-agent_scc').val(data.social_code);
                                // 可选：设置为只读或添加成功样式
                                $('#c-agent_name, #c-agent_scc').addClass('bg-success');
                                Toastr.success(response.msg || "查询成功");
                                // 可选：自动跳转到下一个字段
                                setTimeout(function() {
                                    $('#c-agent_name').focus();
                                }, 300);
                            } else {
                                // 未备案或查询失败
                                Toastr.error(response.msg || "该单位未备案，请先进行备案");
                                // 清空相关字段
                                $('#c-agent_name, #c-agent_scc').val('');
                                $('#c-agent_name, #c-agent_scc').removeClass('bg-success');
                                // 可选：清空并重新聚焦到单位代码输入框
                                $('#c-agent_code').val('').focus();
                            }
                        },
                        error: function(xhr, status, error) {
                            Toastr.error("网络错误，请稍后重试");
                            console.error("AJAX Error:", error);
                        },
                        complete: function() {
                            // 恢复状态
                            $input.removeClass('loading');
                            $submitBtn.prop('disabled', false);
                        }
                    });
                }
            });
        },
		bindEbcCodeSearch: function () {
            // 监听申报单位代码输入框的回车事件
            $('#c-ebc_code').on('keypress', function(e) {
                if(e.which === 13) { // 回车键
                    e.preventDefault(); // 阻止表单提交
                    var ebcCode = $(this).val().trim();
           
                    if(!ebcCode) {
                        Toastr.warning("请输入申报单位代码");
                        return false;
                    }
                    // 显示加载状态
                    var $input = $(this);
                    var $submitBtn = $("form[role=form]").find("button[type=submit]");
                    $input.addClass('loading');
                    $submitBtn.prop('disabled', true);
                    
                    // 发起AJAX请求
                    $.ajax({
                        url: $(this).data('url'), // 从 data-url 获取
                        type: 'POST',
                        data: {
                            ebc_code: ebcCode
                        },
                        dataType: 'json',
                        success: function(response) {
                            if(response.code === 1) {
                                // 查询成功，有备案记录
                                var data = response.data;
                                // 给单位名称和社会信用代码赋值
                                $('#c-ebc_name').val(data.ebc_name);
                                $('#c-trade_scc').val(data.social_code);
                                // 可选：设置为只读或添加成功样式
                                $('#c-ebc_name, #c-trade_scc').addClass('bg-success');
                                Toastr.success(response.msg || "查询成功");
                                // 可选：自动跳转到下一个字段
                                setTimeout(function() {
                                    $('#c-agent_name').focus();
                                }, 300);
                            } else {
                                // 未备案或查询失败
                                Toastr.error(response.msg || "该单位未备案，请先进行备案");
                                // 清空相关字段
                                $('#c-ebc_name, #c-trade_scc').val('');
                                $('#c-ebc_name, #c-trade_scc').removeClass('bg-success');
                                // 可选：清空并重新聚焦到单位代码输入框
                                $('#c-ebc_code').val('').focus();
                            }
                        },
                        error: function(xhr, status, error) {
                            Toastr.error("网络错误，请稍后重试");
                            console.error("AJAX Error:", error);
                        },
                        complete: function() {
                            // 恢复状态
                            $input.removeClass('loading');
                            $submitBtn.prop('disabled', false);
                        }
                    });
                }
            });
        },
		bindCountrySearch: function () {
            // 监听申报单位代码输入框的回车事件
            $('#c-country_cname').on('keypress', function(e) {
                if(e.which === 13) { // 回车键
                    e.preventDefault(); // 阻止表单提交
                    var country = $(this).val().trim();
           
                    if(!country) {
                        Toastr.warning("请输入国家名称或代码");
                        return false;
                    }
                    // 显示加载状态
                    var $input = $(this);
                    var $submitBtn = $("form[role=form]").find("button[type=submit]");
                    $input.addClass('loading');
                    $submitBtn.prop('disabled', true);
                    
                    // 发起AJAX请求
                    $.ajax({
                        url: $(this).data('url'), // 从 data-url 获取
                        type: 'POST',
                        data: {
                            country_data: country
                        },
                        dataType: 'json',
                        success: function(response) {
                            if(response.code === 1) {
                                // 查询成功，有备案记录
                                var data = response.data;
                                // 给单位名称和社会信用代码赋值
                                $('#c-country_cname').val(data.country_cname);
                                $('#c-consignee_country').val(data.consignee_country);
                                $('#c-country_code').val(data.country_code);
                                Toastr.success(response.msg || "查询成功");
                                // 可选：自动跳转到下一个字段
                                setTimeout(function() {
                                    $('#c-traf_name').focus();
                                }, 300);
                            } else {
                                // 未备案或查询失败
                                Toastr.error(response.msg || "国家名称或代码未查询到");
                                // 清空相关字段
                                $('#c-consignee_country, #c-country_code').val('');
                                // 可选：清空并重新聚焦到单位代码输入框
                                $('#c-country_cname').val('').focus();
                            }
                        },
                        error: function(xhr, status, error) {
                            Toastr.error("网络错误，请稍后重试");
                            console.error("AJAX Error:", error);
                        },
                        complete: function() {
                            // 恢复状态
                            $input.removeClass('loading');
                            $submitBtn.prop('disabled', false);
                        }
                    });
                }
            });
        },
		bindOwnerCodeSearch: function () {
            // 监听申报单位代码输入框的回车事件
            $('#c-owner_code').on('keypress', function(e) {
                if(e.which === 13) { // 回车键
                    e.preventDefault(); // 阻止表单提交
                    var ownerCode = $(this).val().trim();
           
                    if(!ownerCode) {
                        Toastr.warning("请输入申报单位代码");
                        return false;
                    }
                    // 显示加载状态
                    var $input = $(this);
                    var $submitBtn = $("form[role=form]").find("button[type=submit]");
                    $input.addClass('loading');
                    $submitBtn.prop('disabled', true);
                    
                    // 发起AJAX请求
                    $.ajax({
                        url: $(this).data('url'), // 从 data-url 获取
                        type: 'POST',
                        data: {
                            owner_code: ownerCode
                        },
                        dataType: 'json',
                        success: function(response) {
                            if(response.code === 1) {
                                // 查询成功，有备案记录
                                var data = response.data;
                                // 给单位名称和社会信用代码赋值
                                $('#c-owner_name').val(data.owner_name);
                                $('#c-owner_scc').val(data.owner_scc);
                                // 可选：设置为只读或添加成功样式
                                $('#c-owner_name, #c-owner_scc').addClass('bg-success');
                                Toastr.success(response.msg || "查询成功");
                                // 可选：自动跳转到下一个字段
                                setTimeout(function() {
                                    $('#c-agent_name').focus();
                                }, 300);
                            } else {
                                // 未备案或查询失败
                                Toastr.error(response.msg || "该单位未备案，请先进行备案");
                                // 清空相关字段
                                $('#c-owner_name, #c-owner_scc').val('');
                                $('#c-owner_name, #c-owner_scc').removeClass('bg-success');
                                // 可选：清空并重新聚焦到单位代码输入框
                                $('#c-port_code').val('').focus();
                            }
                        },
                        error: function(xhr, status, error) {
                            Toastr.error("网络错误，请稍后重试");
                            console.error("AJAX Error:", error);
                        },
                        complete: function() {
                            // 恢复状态
                            $input.removeClass('loading');
                            $submitBtn.prop('disabled', false);
                        }
                    });
                }
            });
        }
    };
    return Controller;
});
