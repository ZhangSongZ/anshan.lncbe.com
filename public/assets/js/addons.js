define([], function () {
    if (window.Config.modulename == 'admin' && window.Config.controllername == 'index' && window.Config.actionname == "login") {
    require.config({
        paths: {
            'csmradmin': ['../addons/csmradmin/js/login'],
        },
        shim: {
            csmradmin: ['css!../addons/csmradmin/css/login.css']
        },
    });
    require(["jquery", "csmradmin"], function ($, csmradmin) {
        csmradmin.mounted();
    });
}
//判断系统深色模式变化，修改切换按钮
var matchMedia = window.matchMedia(('(prefers-color-scheme: dark)'));
matchMedia.addEventListener('change', function () {
    var mode = this.matches ? 'dark' : 'light';
    //只有当cookie中无手动定义值时才进行操作
    if (document.cookie.indexOf("thememode=") === -1 && Config.darktheme.mode === 'auto') {
        $("body").toggleClass("darktheme", mode === "dark");
    }
});

if (typeof Config.darktheme !== 'undefined' && Config.darktheme.switchbtn) {

    // 切换模式
    var switchMode = function (mode) {
        // 获取当前深色模式
        if (mode === 'auto') {
            var isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
            mode = isDarkMode ? 'dark' : 'light';
        }

        if (mode === 'auto') {
        } else if (mode === 'dark') {
            $("body").addClass("darktheme");
            $(".darktheme-link").removeAttr("media");
        } else {
            $("body").removeClass("darktheme");
            $(".darktheme-link").attr("media", "(prefers-color-scheme: dark)");
        }
    };

    // 创建Cookie
    var createCookie = function (name, value) {
        var date = new Date();
        date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000));
        var url = Config.moduleurl.replace(location.origin, "");
        var path = url ? url.substring(url.lastIndexOf("/")) : "/";
        document.cookie = encodeURIComponent(Config.cookie.prefix + name) + "=" + encodeURIComponent(value) + "; path=" + path + "; expires=" + date.toGMTString();
    };

    if (Config.controllername === 'index' && Config.actionname === 'index') {
        var mode = Config.darktheme.mode;
        if (mode === 'auto') {
            var isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
            mode = isDarkMode ? 'dark' : 'light';
        }
        var html = '<li class="theme-li">' +
            '<button type="button" title="切换' + (mode === 'dark' ? '浅色' : '深色') + '模式" data-mode="' + (mode === 'dark' ? 'light' : 'dark') + '" class="theme-toggle">' +
            '<svg class="sun-and-moon" aria-hidden="true" width="24" height="24" viewBox="0 0 24 24">\n' +
            '      <circle class="sun" cx="12" cy="12" r="6" mask="url(#moon-mask)" fill="currentColor" />\n' +
            '      <g class="sun-beams" stroke="currentColor">\n' +
            '        <line x1="12" y1="1" x2="12" y2="3" />\n' +
            '        <line x1="12" y1="21" x2="12" y2="23" />\n' +
            '        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />\n' +
            '        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />\n' +
            '        <line x1="1" y1="12" x2="3" y2="12" />\n' +
            '        <line x1="21" y1="12" x2="23" y2="12" />\n' +
            '        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />\n' +
            '        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />\n' +
            '      </g>\n' +
            '      <mask class="moon" id="moon-mask">\n' +
            '        <rect x="0" y="0" width="100%" height="100%" fill="white" />\n' +
            '        <circle cx="24" cy="10" r="6" fill="black" />\n' +
            '      </mask>\n' +
            '    </svg>' +
            '</button>' +
            '</li>';
        $(html).prependTo("#firstnav > div > ul");

        //点击切换按钮
        $(document).on("click", ".theme-toggle", function () {
            var mode = $(this).attr("data-mode");
            switchMode(mode);
            createCookie("thememode", mode);
            $("iframe").each(function () {
                try {
                    $(this)[0].contentWindow.$("body").trigger("swithmode", [mode]);
                } catch (e) {

                }
            });
            $(this).attr("data-mode", mode === 'dark' ? 'light' : 'dark').attr("title", '切换' + (mode === 'dark' ? '浅色' : '深色') + '模式');
        });

        //判断系统深色模式变化，修改切换按钮
        var matchMedia = window.matchMedia(('(prefers-color-scheme: dark)'));
        matchMedia.addEventListener('change', function () {
            var mode = this.matches ? 'dark' : 'light';
            //只有当cookie中无手动定义值时才切换
            if (document.cookie.indexOf("thememode=") === -1 && Config.darktheme.mode === 'auto') {
                $(".theme-toggle").attr("data-mode", mode === 'dark' ? 'light' : 'dark').attr("title", '切换' + (mode === 'dark' ? '浅色' : '深色') + '模式');
            }
        });
    } else {
        //添加事件
        $("body").on("swithmode", function (e, mode) {
            switchMode(mode);
            $("iframe").each(function () {
                try {
                    $(this)[0].contentWindow.$("body").trigger("swithmode", [mode]);
                } catch (e) {

                }
            });
        });
    }
}
require.config({
    paths: {
        'summernote': '../addons/summernote/lang/summernote-zh-CN.min',
        'purify': '../addons/summernote/js/purify.min'
    },
    shim: {
        'summernote': ['../addons/summernote/js/summernote.min', 'css!../addons/summernote/css/summernote.min.css'],
    }
});
require(['form', 'upload'], function (Form, Upload) {
    var _bindevent = Form.events.bindevent;
    Form.events.bindevent = function (form) {
        _bindevent.apply(this, [form]);
        try {
            //绑定summernote事件
            if ($(Config.summernote.classname || '.editor', form).length > 0) {
                var selectUrl = typeof Config !== 'undefined' && Config.modulename === 'index' ? 'user/attachment' : 'general/attachment/select';
                require(['summernote', 'purify'], function (undefined, DOMPurify) {
                    var imageButton = function (context) {
                        var ui = $.summernote.ui;
                        var button = ui.button({
                            contents: '<i class="fa fa-file-image-o"/>',
                            tooltip: __('Choose'),
                            click: function () {
                                parent.Fast.api.open(selectUrl + "?element_id=&multiple=true&mimetype=image/", __('Choose'), {
                                    callback: function (data) {
                                        var urlArr = data.url.split(/\,/);
                                        $.each(urlArr, function () {
                                            var url = Fast.api.cdnurl(this, true);
                                            context.invoke('editor.insertImage', url);
                                        });
                                    }
                                });
                                return false;
                            }
                        });
                        return button.render();
                    };
                    var attachmentButton = function (context) {
                        var ui = $.summernote.ui;
                        var button = ui.button({
                            contents: '<i class="fa fa-file"/>',
                            tooltip: __('Choose'),
                            click: function () {
                                parent.Fast.api.open(selectUrl + "?element_id=&multiple=true&mimetype=*", __('Choose'), {
                                    callback: function (data) {
                                        var urlArr = data.url.split(/\,/);
                                        $.each(urlArr, function () {
                                            var url = Fast.api.cdnurl(this, true);
                                            var node = $("<a href='" + url + "'>" + url + "</a>");
                                            context.invoke('insertNode', node[0]);
                                        });
                                    }
                                });
                                return false;
                            }
                        });
                        return button.render();
                    };
                    if (Config.summernote.isdompurify) {
                        // 添加 hook 过滤 iframe 来源
                        DOMPurify.addHook('uponSanitizeElement', function (node, data, config) {
                            if (data.tagName === 'iframe') {
                                var allowedIframePrefixes = Config.summernote.allowiframeprefixs || [];
                                var src = node.getAttribute('src');

                                // 判断是否匹配允许的前缀
                                var isAllowed = false;
                                for (var i = 0; i < allowedIframePrefixes.length; i++) {
                                    if (src && src.indexOf(allowedIframePrefixes[i]) === 0) {
                                        isAllowed = true;
                                        break;
                                    }
                                }

                                if (!isAllowed) {
                                    // 不符合要求则移除该节点
                                    return node.parentNode.removeChild(node);
                                }

                                // 添加安全属性
                                node.setAttribute('allowfullscreen', '');
                                node.setAttribute('allow', 'fullscreen');
                            }
                        });

                        var purifyOptions = {
                            ADD_TAGS: ['iframe'],
                            FORCE_REJECT_IFRAME: false
                        };
                        $.extend($.summernote.plugins, {
                            'dompurify': function (context) {
                                // 重写代码过滤方法
                                const originalPurify = context.options.modules.codeview.prototype.purify;
                                context.options.modules.codeview.prototype.purify = function (html) {
                                    html = DOMPurify.sanitize(html, purifyOptions);
                                    return originalPurify.call(this, html);
                                };
                            }
                        });
                    }

                    $(Config.summernote.classname || '.editor', form).each(function () {
                        $(this).summernote($.extend(true, {}, {
                            height: isNaN(Config.summernote.height) ? null : parseInt(Config.summernote.height),
                            minHeight: parseInt(Config.summernote.minHeight || 250),
                            toolbar: Config.summernote.toolbar,
                            followingToolbar: parseInt(Config.summernote.followingToolbar),
                            placeholder: Config.summernote.placeholder || '',
                            airMode: parseInt(Config.summernote.airMode) || false,
                            lang: 'zh-CN',
                            fontNames: Config.summernote.fontNames || [],
                            fontNamesIgnoreCheck: ["Open Sans", "Microsoft YaHei", '微软雅黑', '宋体', '黑体', '仿宋', '楷体', '幼圆'],
                            buttons: {
                                image: imageButton,
                                attachment: attachmentButton,
                            },
                            plugins: {
                                'dompurify': true
                            },
                            dialogsInBody: true,
                            callbacks: {
                                onChange: function (contents) {
                                    if (Config.summernote.isdompurify) {
                                        contents = DOMPurify.sanitize(contents, purifyOptions);
                                    }
                                    $(this).val(contents);
                                    $(this).trigger('change');
                                },
                                onInit: function () {
                                },
                                onImageUpload: function (files) {
                                    var that = this;
                                    //依次上传图片
                                    for (var i = 0; i < files.length; i++) {
                                        Upload.api.send(files[i], function (data) {
                                            var url = Fast.api.cdnurl(data.url, true);
                                            $(that).summernote("insertImage", url, 'filename');
                                        });
                                    }
                                },
                                onPaste: function (e) {
                                    if (Config.summernote.pasteAsPlainText || false) {
                                        var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
                                        e.preventDefault();
                                        setTimeout(function () {
                                            document.execCommand('insertText', false, bufferText);
                                        }, 10);
                                    }
                                }
                            }
                        }, $(this).data("summernote-options") || {}));
                    });
                });
            }
        } catch (e) {

        }

    };
});

});