<?php
$cmds = array();
date_default_timezone_set("Asia/Shanghai");

$max_depth = 1;

if(isset($argv[1])){
    $max_depth = $argv[1];
}
$end = date("Ymd", strtotime("-1 day"));
if(isset($argv[2])){
    $end = $argv[2];
}
$date_end = " --date_end = $end";
error_reporting(E_ALL);
$cmds[] = "/usr/bin/php event_map_shell.php dailyreading_click client level --max_depth=$max_depth --path=/mnt/applog/dict-visit/ --type=rewrite";
$cmds[] = "/usr/bin/php event_map_shell.php daily_follow_reading_sentence_show client contentId --max_depth=$max_depth --path=/mnt/applog/dict-visit/ --type=rewrite";
$cmds[] = "/usr/bin/php event_map_shell.php daily_follow_reading_sentence_record client contentId --max_depth=$max_depth --path=/mnt/applog/dict-visit/ --type=rewrite";
$cmds[] = "/usr/bin/php event_map_shell.php daily_follow_reading_upload_succeed client readingId --max_depth=$max_depth --path=/mnt/applog/dict-visit/ --type=rewrite";
$cmds[] = "/usr/bin/php event_map_shell.php daily_follow_reading_upload_succeed client  --max_depth=$max_depth --path=/mnt/applog/dict-visit/ --type=rewrite";
$cmds[] = "/usr/bin/php event_map_shell.php dailyreading_list_click client level --max_depth=$max_depth --path=/mnt/applog/dict-visit/ --type=rewrite";
$cmds[] = "/usr/bin/php event_map_shell.php dailyreading_list_click client contentId --max_depth=$max_depth --path=/mnt/applog/dict-visit/ --type=rewrite";
$cmds[] = "/usr/bin/php event_map_shell.php dailyreading_list_click client position contentId --max_depth=$max_depth --path=/mnt/applog/dict-visit/ --type=rewrite";
// /usr/bin/php nginx_log_shell.php listen.iciba.com --urlpre=listening/read/historyContent --max_depth=$max_depth --path=/home/chengxueming/temp_event/ --type=rewrite --delimiter=client,level,page --date_end=20170410 --source_path=/mnt/nginxlog/nginx/
// /usr/bin/php event_map_shell.php dailyreading_result_item_click client contentId --max_depth=$max_depth --path=/mnt/applog/dict-visit/ --type=rewrite
// cd ../event_shell/;/usr/bin/php event_map_shell.php dailyreading_result_item_click client contentId --max_depth=1 --path=/home/chengxueming/dict-visit/ --type=rewrite --date_end=20170321
// /usr/bin/php nginx_log_shell.php service.iciba.com --urlpre=yuedu/book/getBookLabelRecommend --max_depth=1 --path=/mnt/nginxlog_all/nginx/ --type=rewrite --delimiter=client,label,page --date_end=20170410
foreach($cmds as $i=>$cmd){
    $begin = time();
    echo "$i <<begin exec $cmd $date_end\n";
    echo "***************************\n";
    $res = shell_exec($cmd);
    echo $res."\n";
    echo "***************************\n";
    $cost = time() - $begin;
    echo "$i >>exec $cmd total cost $cost second\n";
}
