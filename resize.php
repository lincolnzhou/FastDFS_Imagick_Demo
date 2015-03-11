<?php
$gets = $_GET;
$fileId = $gets['fileid'];


/**
 * 返回404
 */
function err404()
{
    header('HTTP/1.1 404 Not Found');
    header("status: 404 Not Found");
    exit;
}
?>