<?php
/**
 * @name            FDFS.php
 * @description     FastDFS操作封装类
 * @author          周仕林 2015/3/3
 * TODO 上线后将异常捕获去掉
 */
class FDFS {
    /**
     * 连接服务器信息
     * @var array
     */
    protected $server;

    /**
     * 连接tracker信息
     * @var array
     */
    protected $tracker;

    /**
     * 连接storage信息
     * @var array
     */
    protected $storage;

    /**
     * 错误信息
     * @var string
     */
    protected $error = '';

    /**
     * 构造函数
     * @author 周仕林 2015/3/3
     */
    function FDFS() {
        if (!extension_loaded('fastdfs_client')) {
            $this->error = '服务器未开启fastdfs_client扩展';
            throw new Exception('服务器未开启fastdfs_client扩展');
        }

        $this->tracker = fastdfs_tracker_get_connection(); //连接tracker服务器
        $this->storage = fastdfs_tracker_query_storage_store(); //连接storage服务器
        $this->server = fastdfs_connect_server($this->storage['ip_addr'], $this->storage['port']);

        //判断是否已经连接上服务器
        if (!$this->server) {
            error_log("错误码: " . fastdfs_get_last_error_no() . ", 错误信息: " . fastdfs_get_last_error_info());
            $this->error = 'FastDFS服务未启动';
            throw new Exception('FastDFS服务未启动');
        }

        $this->storage['sock'] = $this->server['sock'];
    }

    /**
     * 上传文件
     * @param array $file 文件信息数组，通常为$_FILES数组
     * @return bool
     * @author 周仕林 2015/3/3
     * TODO 暂时只支持单个文件上传，之后可考虑使用foreach处理
     */
    function upload($file) {
        //判断是否有文件上传
        if (empty($file)) {
            $this->error = '暂无上传文件';
            return false;
        }

        $tempName = $file['tmp_name']; //判断上传文件临时名称
        $realName = $file['name']; //判断上传文件名
        $fileName = dirname($tempName) . '/' . $realName; //新的文件名

        //TODO 可考虑直接将临时文件上传，不需要重命名，因为临时文件夹中会出现同名冲突
        @rename($tempName, $fileName); //使用重命名
        $fileInfo = fastdfs_storage_upload_by_filename($fileName, null, array(), null, $this->tracker, $this->storage);

        //判断上传是否成功
        if ($fileInfo) {
            $groupName = $fileInfo['group_name'];
            $serverFileName = $fileInfo['filename'];
            $serverFileInfo = fastdfs_get_file_info($groupName, $serverFileName);

            return array(
                'file_id' => $serverFileName,
                'group_name' => $groupName,
                'source_ip_address' => $serverFileInfo['source_ip_addr'],
                'file_name' => $realName
            );
        } else {
            error_log("错误码: " . fastdfs_get_last_error_no() . ", 错误信息: " . fastdfs_get_last_error_info());
            $this->error = '上传服务器后存储失败';
            //throw new Exception('上传服务器后存储失败');

            return false;
        }
    }

    /**
     * 下载文件
     * @param $groupName
     * @param $fileId
     * @author 周仕林 2015/3/3
     */
    function download($groupName, $fileId) {
        $fileContent = fastdfs_storage_download_file_to_buff($groupName, $fileId);

        return $fileContent;
    }

    /**
     * 删除文件
     * @author 周仕林 2015/3/3
     */
    public function delete() {
    }
}
