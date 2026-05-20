<?php

namespace app\common\controller;


ini_set('max_execution_time', 600); //延长请求超时时间
/********************************************

 * MODULE:FTP类

 *******************************************/

class ftp
{
    public $off;             // 返回操作状态(成功/失败)
    public $conn_id;           // FTP连接

    /**
     * 方法：FTP连接
     * @FTP_HOST -- FTP主机
     * @FTP_PORT -- 端口
     * @FTP_USER -- 用户名
     * @FTP_PASS -- 密码
     */
    function __construct($FTP_HOST,$FTP_PORT,$FTP_USER,$FTP_PASS)
    {
        $this->conn_id = @ftp_connect($FTP_HOST,$FTP_PORT) or die("FTP服务器连接失败");
        @ftp_login($this->conn_id,$FTP_USER,$FTP_PASS) or die("FTP服务器登陆失败");
        @ftp_pasv($this->conn_id,1); // 打开被动模拟
    }

    /**
     * 方法：上传文件
     * @path  -- 本地路径
     * @newpath -- 上传路径
     * @type  -- 若目标目录不存在则新建
     */
    function up_file($path,$newpath,$type=true)
    {
        if($type) $this->dir_mkdirs($newpath);
        $this->off = @ftp_put($this->conn_id,$newpath,$path,FTP_BINARY);
        if(!$this->off) echo "文件上传失败,请检查权限及路径是否正确！";
    }

    /**
     * 方法：下载文件
     * @path  -- 下载到本地的路径
     * @oldpath -- 文件远程路径
     * @type  -- 若目标目录不存在则新建
     */
    function down_file($path,$oldpath)
    {
        $code=@ftp_get($this->conn_id, $path, $oldpath, FTP_BINARY);
        if(!$code) echo "文件下载失败,请检查权限及路径是否正确！";
    }

    /**
     * 方法：移动文件
     * @path  -- 原路径
     * @newpath -- 新路径
     * @type  -- 若目标目录不存在则新建
     */
    function move_file($path,$newpath,$type=true)
    {
        if($type) $this->dir_mkdirs($newpath);
        $this->off = @ftp_rename($this->conn_id,$path,$newpath);
        if(!$this->off) echo "文件移动失败,请检查权限及原路径是否正确！";
    }

    /**
     * 方法：复制文件
     * 说明：由于FTP无复制命令,本方法变通操作为：下载后再上传到新的路径
     * @path  -- 原路径
     * @newpath -- 新路径
     * @type  -- 若目标目录不存在则新建
     */
    function copy_file($path,$newpath,$type=true)
    {
        $downpath = "/test_tmp";
        $this->off = @ftp_get($this->conn_id,$downpath,$path,FTP_BINARY);// 下载
        if(!$this->off) echo "文件复制失败,请检查权限及原路径是否正确！";
        $this->up_file($downpath,$newpath,$type);
    }

    /**
     * 方法：删除文件
     * @path -- 路径
     */
    function del_file($path)
    {
        $this->off = @ftp_delete($this->conn_id,$path);
        if(!$this->off) echo "文件删除失败,请检查权限及路径是否正确！";
    }

    /**
     * 方法：生成目录
     * @path -- 路径
     */
    function dir_mkdirs($path)
    {
        $path_arr = explode('/',$path);       // 取目录数组
        $file_name = array_pop($path_arr);      // 弹出文件名
        $path_p = count($path_arr);        // 取层数
        foreach($path_arr as $val)          // 创建目录
        {
            if(@ftp_chdir($this->conn_id,$val) == FALSE)
            {
                $tmp = @ftp_mkdir($this->conn_id,$val);
                if($tmp == FALSE)
                {
                    echo "目录创建失败,请检查权限及路径是否正确！";
                    exit;
                }
                @ftp_chdir($this->conn_id,$val);
            }
        }
        for($i=1;$i<=$path_p;$i++)         // 回退到根
        {
            @ftp_cdup($this->conn_id);
        }
    }

    /**
     * 方法：打印文件目录
     * $directory 希望查看的目录
     * return  Array
     */
    function list($directory='.'){
        return @ftp_nlist($this->conn_id, $directory);
    }

    /**
     * 方法：关闭FTP连接
     */
    function close()
    {
        @ftp_close($this->conn_id);
    }
}


// 使用事例：

/************************************** 测试 ***********************************

$ftp = new ftp('YourFtp-IpPath','ports','username','password');     // 打开FTP连接

//$ftp->list('directory')    // 罗列目录

//$ftp->up_file('localPath','ftpPath');     // 上传文件

//$ftp->down_file('localPath','ftpPath');     // 下载文件

//$ftp->move_file('oldPath','newPath');        // 移动文件

//$ftp->copy_file('oldPath','newPath');    // 复制文件

//$ftp->del_file('filePath');                // 删除文件

$ftp->close();                       // 关闭FTP连接

//******************************************************************************/