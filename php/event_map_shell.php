<?php
/**
 * Created by PhpStorm.
 * User: CHENGXUEMING
 * Date: 17-3-4
 * Time: ����11:59
 */
require "config.php";
require "common_helper.php";
require "xlogres.php";
include 'commandline.php';

ini_set('memory_limit', '8996M');

date_default_timezone_set("Asia/Shanghai");

$extra_argv = deal_argvs($argv);
$event_name = $argv[1];
$dir = "/home/chengxueming/collection/";
if(isset($extra_argv["collection_path"]) && !empty($extra_argv["collection_path"])){
    $dir = $extra_argv["collection_path"];
}
$filename = $dir.$argv[1];
unset($argv[1]);
unset($argv[0]);

$validRowNum = 0;
$conNum = 0;
$calNum = 0;
$content = array();
$date_map = array();
$i = 0;

$date_end = date("Ymd",strtotime("-1 day"));
if(isset($extra_argv["date_end"])){
    $date_end = $extra_argv["date_end"];
}
$date_begin = $date_end;

if(isset($extra_argv["max_depth"])){
    $max_depth = $extra_argv["max_depth"] - 1;
    if($max_depth < 0){
        echo "wrong max_depth";
        exit;
    }
    $date_begin = date("Ymd",strtotime("-$max_depth day $date_end"));
}
echo "begin date:$date_begin end_date:$date_end\n";

if(isset($extra_argv["type"]) && $extra_argv["type"] == "rewrite"){
    $begin = time();
    echo "begin create file\n";
    create_file($event_name,$date_begin,$date_end,$filename);
    echo "create file cost time:".(time() - $begin)."second\n";
}
error_reporting(E_ALL);
$vender = file_get_contents($filename);
$vender_sp = explode("\n", $vender);

//$i = 1;
$begin = time();
$db_type_arr = array();
echo "begin analyse file\n";
$xres = new xlogres();
foreach($vender_sp as $line) {
    $items = explode("\t", $line);
    $jsonArr = json_decode($items[1], true);
    if(empty($jsonArr)){
        continue;
    }
    $timestamp = $jsonArr["timestamp"];
    if($timestamp < strtotime($date_begin) || $timestamp >= (strtotime($date_end) + 86400)){
        continue;
    }
    if ("" == $jsonArr["remark"]) {
        $conNum++;
        continue;
    }
    $jsonArrRemark = json_decode($jsonArr["remark"], true);
    if($jsonArrRemark["type"] != $event_name){
        echo "ahha bug".$jsonArrRemark["type"]."\n";
        continue;
    }
    $jsonArrRemark["client"] = $jsonArr["client"];
    $jsonArrRemark["version"] =  $jsonArr["v"];
    $date = date("Ymd",$timestamp);
    $uuid = $jsonArr["uuid"];
    $flag = 0;
    $temp_argv = $argv;

    $xres->adduuid($temp_argv, $jsonArrRemark, $date);
}
//unlink($filename);
$delimiter_arr = $argv;
echo "analyse file cost time:".(time() - $begin)."second\n";
echo "output_to_file begin\n";
$this->res->result_to_file($event_name.".out");
echo "output_to_file end\n";
$table_pre = $event_name;
$begin = time();
$output_config = $config;
if(isset($extra_argv["outputDB"]))
{
    $output_config = $$extra_argv["outputDB"];
}
output_to_db($this->res->getPVUVMap(),$output_config);
echo "outtodb cost time:".(time() - $begin)."second\n";

function create_file($event_name,$begin_date,$end_date,$file_name){
    global $extra_argv;
    $path = "/data/logs/app/dict-visit/";
    $extra_path = "/home/chengxueming/dict-visit/";
    if(isset($extra_argv["path"])){
        $path = $extra_argv["path"];
    }
    if(isset($extra_argv["extra_path"])){
        $extra_path = $extra_argv["extra_path"];
    }
    $date = $end_date;
    $data_path = $path;
    if(isset($extra_argv["extra_path"]) && (strtotime("now") - strtotime($date))/86400 > 8){
        $cmd = "./help_dict_vist.sh $date $extra_path $path";
        echo $cmd."\n";
        shell_exec($cmd);
        $data_path = $extra_path;
    }
    $cmd = "find $data_path|grep event-$date|xargs grep $event_name > $file_name";
    echo $cmd."\n";
    shell_exec($cmd);
    if($date != $begin_date){
        $i = 1;
        while(1){
            $date = date("Ymd",strtotime("-$i day $end_date"));
            if(isset($extra_argv["extra_path"]) && (strtotime("now") - strtotime($date))/86400 > 8){
                $cmd = "./help_dict_vist.sh $date $extra_path $path";
                echo $cmd."\n";
                shell_exec($cmd);
                $data_path = $extra_path;
            }
            $cmd = "find $data_path|grep event-$date|xargs grep $event_name >> $file_name";
            echo $cmd."\n";
            shell_exec($cmd);
            $i++;
            if($date == $begin_date){
                break;
            }
        }
    }
}