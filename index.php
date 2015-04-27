<?php
/**
 * @name            resize.php
 * @description     图片处理
 * @author          周仕林 2015/3/11
 */
$imagePath = '/var/www/fastdfs/data/images';
$url = $_SERVER['PATH_INFO'];

//匹配Url是否正确
if (!preg_match('/^\/([0-9a-z_\-\/]{40})\.(jpg|png|gif|bmp)(@{0,1})(([0-9]{1,4})_([0-9]{1,4})_([0-9]{1,4}).(jpg|png|gif|bmp)){0,1}$/i', $url, $match)) {
    error404();
}

$fileId = $match[1]; //文件Id除后缀名
$fileIdArray = explode('/', $fileId);
$prefixFileName = $fileIdArray[3]; //文件前缀名
$ext = $match[2]; //文件后缀名
$originFileName = $ext == '' ? $fileId : $fileId . '.' . $ext;//文件原始名称
$fileName = $match[1] . $match[2]; //文件名
$thumbFormat = $match[4]; //图片处理格式
$width = (int)$match[5]; //宽度
$height = (int)$match[6]; //高度
$quantity = (int)$match[7]; //质量
$thumbFileName = $prefixFileName . '_' . $thumbFormat;

//判断图片存储目录是否存在
if (!is_dir($imagePath)) {
    mkdir($imagePath);
}

//判断图片处理参数是否正确
if ($width  && $height && $quantity) {
    $filePath = $imagePath . '/' . $thumbFileName;
    //判断源文件是否存在
	if (file_exists($filePath)) {
        $imgData = file_get_contents($filePath);
        $imagick = new Imagick();
        $imagick->readImageBlob($imgData);
        $format = $imagick->getImageFormat();

        //显示
        header('Content-Type: image/' . $format);
        header('Content-Length: '.strlen($imgData));
        echo $imgData;
	} else {
        $tracker = fastdfs_tracker_get_connection(); //连接tracker服务器
        $storage = fastdfs_tracker_query_storage_store(); //连接storage服务器
        $server = fastdfs_connect_server($storage['ip_addr'], $storage['port']);
        $storage['sock'] = $server['sock'];
        $imgData = fastdfs_storage_download_file_to_buff('group1', $originFileName);
        $imagick = new Imagick();
        $imagick->readImageBlob($imgData);
        $format = $imagick->getImageFormat();
        $imagick->resizeImage($width, $height, Imagick::FILTER_CATROM, 1, true);
        $imagick->setImageCompressionQuality($quantity);
        //去除exif信息
        $imagick->stripImage();
        $imgData = $imagick->getImageBlob();

        if (!file_put_contents($filePath, $imgData)) {
            error_log("缩略图文件保存失败，请检查目录权限");
        }

        //显示
        header('Content-Type: image/' . $format);
        header('Content-Length: '.strlen($imgData));
        //echo fread($imgData, strlen($imgData));
        echo $imgData;
        exit;
    }
} else {
    error404();
}

/**
 * 返回Error404
 */
function error404() {
    header('HTTP/1.1 404 Not Found');
    header("status: 404 Not Found");
    exit;
}
