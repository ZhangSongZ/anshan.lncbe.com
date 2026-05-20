<?php

namespace addons\csmradmin;

use app\common\library\Menu;
use think\Addons;

/**
 * 插件
 */
class Csmradmin extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name' => 'csmradmin',
                'title' => '管理员注册审核',
                'sublist' => [
                    [
                        'name' => 'csmradmin/adminapply',
                        'title' => '帐号审核',
                        'icon' => 'fa fa-meetup',
                        'sublist' => [
                            [
                                'name' => 'csmradmin/adminapply/index',
                                'title' => '查看'
                            ],
                            [
                                'name' => 'csmradmin/adminapply/add',
                                'title' => '添加'
                            ],
                            [
                                'name' => 'csmradmin/adminapply/edit',
                                'title' => '修改'
                            ],
                            [
                                'name' => 'csmradmin/adminapply/del',
                                'title' => '删除'
                            ]
                        ]
                    ],
                ]
            ]
        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete("csmradmin");
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable("csmradmin");
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable("csmradmin");
        return true;
    }

    /**
     * 实现钩子方法
     * @return mixed
     */
    public function testhook($param)
    {
        // 调用钩子时候的参数信息
        print_r($param);
        // 当前插件的配置信息，配置信息存在当前目录的config.php文件中，见下方
        print_r($this->getConfig());
        // 可以返回模板，模板文件默认读取的为插件目录中的文件。模板名不能为空！
        //return $this->fetch('view/info');
    }

}
