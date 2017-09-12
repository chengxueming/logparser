<?php
/**
 * Created by PhpStorm.
 * User: CHENGXUEMING
 * Date: 17-4-10
 * Time: 下午4:28
 */
// /usr/bin/php nginx_log_shell.php listen.iciba.com --urlpre=listening/read/historyContent --max_depth=$max_depth --path=/home/chengxueming/temp_event/ --type=rewrite --delimiter=client,level,page --date_end=20170410 --source_path=/mnt/nginxlog/nginx/
//1.

include 'commandline.php';
include "xnginxlog.php";
date_default_timezone_set("Asia/Shanghai");


require "config.php";
require "common_helper.php";
ini_set('memory_limit', '8996M');

$args = CommandLine::parseArgs();



//function __construct($url_pre, $date_format, $delimiter_arr, $domain, $path,$date_end,  $max_depth , $collection_path , $source_path = "")
$opts = $args["opts"];
$delimiter_arr = explode(",", $opts["delimiter"]);
$source_path = isset($opts["source_path"])?$opts["source_path"]:"";
$date_end = isset($opts["date_end"])?$opts["date_end"] : date("Ymd", strtotime("-1 day"));
$output_config = isset($opts["outputDB"])?$$opts["outputDB"]:$config;
$logparase = new xnginxlog($opts["urlpre"], $opts["date_format"], $delimiter_arr, $args["args"][0], $opts["path"], $date_end, $opts["max_depth"],$opts["collection_path"],$source_path);

$logparase->runSpaceLess();

$value_map = $logparase->res->valuemap;
$table_pre = $logparase->table_pre;
output_to_db($logparase->res->getPVUVMap(), $output_config);
