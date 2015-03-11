<?php
/**
 * @name            upload.php
 * @description     文件上传接口文件
 * @author          周仕林 2015/3/4
 */
error_reporting(E_ALL);
include_once("fastdfs.php");

//判断上传文件
if ($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_FILES['file'])) {
    $file = $_FILES['file'];
    $fastDFS = new FDFS();
    $result = $fastDFS->upload($_FILES['file']);

    if ($result) {
    	$result['code'] = 200;
		exit(json_encode($result));
    } else {
    	$result = array('code' => 400, 'message' => $fastDFS->getError());
    	exit(json_encode($result));
    }
}
?>