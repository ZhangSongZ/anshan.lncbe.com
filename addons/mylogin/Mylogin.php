<?php

namespace addons\mylogin;

use think\Addons;

/**
 * 插件
 */
class Mylogin extends Addons
{
    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        return true;
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        return true;
    }

    /**
     * 脚本替换---注释留做参考
     */
    public function viewFilter(& $content)
    {
        $module = strtolower(request()->module());
        $controllername = \think\Loader::parseName(request()->controller());
        $actionname = strtolower(request()->action());
        $path = str_replace('.', '/', $controllername) . '/' . $actionname;
        $info = get_addon_info('mylogin');

        if ($module == 'admin' && $path == 'index/login' && $info['state'] == 1 && strpos($content,'.system-message') === false)
        {
            //插件配置
            $config = get_addon_config($info['name']);

            //去掉默认样式
            $content = preg_replace_callback('/\<style[\s\S]type\=\"text\/css\"\>([\s\S]*?)<\/style>/i', function ($matches) {
                return '';
            }, $content);
            $content = preg_replace_callback('/class\=\"well\"/i', function ($matches) {
                return '';
            }, $content);

            //注入样式
            $template_txt = file_get_contents(ADDON_PATH . '/' . $info['name'] . '/template/' . $config['theme'] . '/style.html');
            $content = preg_replace_callback('/<!--@formatter:off-->([\s\S]*?)<!--@formatter:on-->/i', function ($matches) use ($template_txt) {
                return $template_txt;
            }, $content);

            //修改head图片
            $login_img = 'assets/addons/' . $info['name'] . '/img/' . $config['theme'] . '/login_image.jpg';
            if (file_exists($login_img)) {
                $template_txt = '<div class="login-head"><img src="/assets/addons/' . $info['name'] . '/img/' . $config['theme'] . '/login_image.jpg"></div>';
            } else {
                $template_txt = '';
            }
            $content = preg_replace_callback('/<div[\s\S]class="login-head">([\s\S]*?)<\/div>/i', function ($matches) use ($template_txt) {
                return $template_txt;
            }, $content);

            //修改头像位置
            $title = config('site.name');
            $logo = $config['logo'] ? cdnurl($config['logo'], true) : '/assets/addons/' . $info['name'] . '/img/logo.png';
            $template_txt = '<h2 class="lg_sec_tit"><img src="' . $logo . '">' . $title . '</h2>';
            $content = preg_replace_callback('/<img[\s\S]id="profile-img"([\s\S]*?)class="profile-name-card"><\/p>/i', function ($matches) use ($template_txt) {
                return $template_txt;
            }, $content);

            //添加ID
            $template_txt = 'class="container" id="mydiv"';
            $content = preg_replace_callback('/class="container"/i', function ($matches) use ($template_txt) {
                return $template_txt;
            }, $content);

            //注入版权信息与script
            $template_txt = '<div class="copyright">' . $config['copyright'] . '</div>';
            $template_txt .= file_get_contents(ADDON_PATH . '/' . $info['name'] . '/template/' . $config['theme'] . '/script.html');
            $content = preg_replace_callback('/<\/body>/i', function ($matches) use ($template_txt) {
                return $template_txt . '</body>';
            }, $content);
        }
    }
}