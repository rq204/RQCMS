<?php
$tempView=$coreView;//不用再去加载模板了
$contentType='Content-Type: text/css; charset=UTF-8';
include RQ_CORE.'/manager/view/css.php';
$csstime=strtotime(date("y-m-d"));
doAction('admin_addcss');
$ETag=md5($cssdata);
cacheControl($csstime,$ETag);
echo $cssdata;
?>