define(['jquery','toastr', 'form', 'template'], function ($,toastr, Form, Template) {
	var Controller = {
		mounted:function(){
			var that = this;
			that._render();
			that._bind();
		},
		_render:function(){
			console.log('_render');
			//添加登录和注册tab
			var dom = $(".well");
			var formhtml = '\
				<div class="login-section">\
					<div class="logon-tab clearfix">\
						<a class="active" id="tab-login">登录</a>\
						<a id="tab-register">注册</a>\
					</div>\
				</div>\
			';
			dom.prepend(formhtml);

			//添加注册的form表单
			var dom2 = $(".login-form");
			var formhtml = '\
				<div class="register-form" style="display:none">\
				   <p class="profile-name-card"></p>\
					<form action="'+window.Config.moduleurl+'/csmradmin/registerajax/register" method="post" id="register-form">\
                        <div class="input-group">\
                            <div class="input-group-addon"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></div>\
                            <input type="text" name="username" class="form-control" id="register-form-username" placeholder="用户名" autocomplete="off" value="" data-rule="用户名:required;length(4~12)" />\
                        </div>\
                        <div class="input-group">\
                            <div class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></div>\
                            <input type="password" name="password" class="form-control" id="register-form-password" placeholder="密码" autocomplete="off" value="" data-rule="密码:required;length(6~16)" />\
                        </div>\
                        <div class="input-group">\
                            <div class="input-group-addon"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></div>\
                            <input type="text" name="nickname" class="form-control" id="register-form-nickname" placeholder="姓名" autocomplete="off" value="" data-rule="姓名:required;length(2~16)" />\
                        </div>\
                         <div class="input-group">\
                            <div class="input-group-addon"><span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></span></div>\
                            <input type="text" name="phone" class="form-control" id="register-form-phone" placeholder="联系电话" autocomplete="off" value="" data-rule="联系电话:required;length(7~16)" />\
                        </div>\
                          <div class="input-group">\
                          <div class="input-group-addon"><span class="glyphicon glyphicon-sound-dolby" aria-hidden="true"></span></div>\
                           <select class="form-control" name="type">\
                                  <option value="电商企业">电商企业</option>\
                                  <option value="电商平台">电商平台</option>\
                                  <option value="物流企业">物流企业</option>\
                                  <option value="报关企业">报关企业</option>\
                                  <option value="监管场所">监管场所</option>\
                                </select>\
                             </div>\
                         <div class="form-group">\
                            <button type="submit" id="btn-register" class="btn btn-success btn-lg btn-block">注 册</button>\
                            <button type="button" id="btn-registered" class="btn btn-lg btn-block">注册成功,请前往登录</button>\
                        </div>\
					</form>\
				</div>\
			';		
			dom2.after(formhtml);


		},
		
// 		 <div class="input-group">\
//                             <div class="input-group-addon"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></div>\
//                             <input type="text" name="email" class="form-control" id="register-form-email" placeholder="邮箱" autocomplete="off" value="" data-rule="邮箱:required;length(6~30)" />\
//                         </div>\
//                         <div class="input-group">\
//                             <div class="input-group-addon"><span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span></div>\
//                             <input type="text" id="register-form-ccode" name="ccode" data-rule="验证码:required;length(4)" class="form-control" placeholder="验证码" />\
//                             <span class="input-group-addon" style="padding:0;border:none;cursor:pointer;" id="ccode-readysend">\
//                                 &nbsp;&nbsp;发送邮件验证码&nbsp;&nbsp;\
//                             </span>\
//                             <span class="input-group-addon" style="padding:0;border:none;cursor:pointer;" id="ccode-sending">\
//                                 &nbsp;&nbsp;邮件发送中&nbsp;&nbsp;\
//                             </span>\
//                             <span class="input-group-addon" style="padding:0;border:none;cursor:pointer;" id="ccode-sended">\
//                                 &nbsp;&nbsp;邮件已经发送&nbsp;&nbsp;\
//                             </span>\
//                         </div>\
		_bind:function(){
			var that = this;
			console.log('_bind');
			$("#tab-login").click(function(){
				console.log('tab login');
				$(".login-form").css("display","block");
				$(".register-form").css("display","none");

				$("#tab-login").addClass("active");
				$("#tab-register").removeClass("active");
				
				// v1.0.1 兼容fa1.2.0版本显示不兼容问题
				$(".profile-img-card").css("display","block");
				$(".csmadmin-otherlogin").css("display","block");
			});			
			$("#tab-register").click(function(){
				console.log('tab register');
				$(".login-form").css("display","none");
				$(".register-form").css("display","block");

				$("#tab-login").removeClass("active");
				$("#tab-register").addClass("active");
				
				// v1.0.1 兼容fa1.2.0版本显示不兼容问题
				$(".profile-img-card").css("display","none");
				$(".csmadmin-otherlogin").css("display","none");
			});
			$("#ccode-readysend").click(function(){
				var email = $("#register-form-email").val();
				console.log("email="+email);
				that._displayemailbtn("#ccode-sending");
                Fast.api.ajax({
                    url: window.Config.moduleurl+'/csmradmin/registerajax/sendccode',
                    type: "post",
                    data: {
                    	email: email
                    },
                }, function (data, ret) {
                    console.log(data);
                    that._displayemailbtn("#ccode-sended");
                    $("#register-form-email").attr("readonly","true");
                    // Toastr.success("发送成功");
                },function(){
                	that._displayemailbtn("#ccode-readysend");
                });
			});
            //本地验证未通过时提示
            $("#register-form").data("validator-options", {
                invalid: function (form, errors) {
                    $.each(errors, function (i, j) {
                        Toastr.error(j);
                    });
                },
                target: '#errtips'
            });

            //为表单绑定事件
            Form.api.bindevent($("#register-form") , function(data, ret){
            	console.log(data);
            	that._displayregbtn("#btn-registered");
                $("#register-form-username").attr("readonly","true");
                $("#register-form-password").attr("readonly","true");
                $("#register-form-nickname").attr("readonly","true");
                $("#register-form-phone").attr("readonly","true");
                // $("#register-form-email").attr("readonly","true");
                // $("#register-form-ccode").attr("readonly","true");
            }, function(data, ret){
            	console.log(data);
            }, function(success, error){
                return true;
            });
		},
		_displayemailbtn:function($key){
            $("#ccode-readysend").css("display","none");
			$("#ccode-sending").css("display","none");
			$("#ccode-sended").css("display","none");
			$($key).css("display","table-cell");
		},
		_displayregbtn:function($key){
            $("#btn-register").css("display","none");
			$("#btn-registered").css("display","none");
			$($key).css("display","table-cell");
		}
	};
	return Controller;
});