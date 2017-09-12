<?php

if(!function_exists("deal_argvs")){
    function deal_argvs(&$argvs){
        $res = array();
        foreach($argvs as $key=>$arg){
            if(strpos($arg,"--") === 0){
                $str = substr($arg,2);
                $temp_arr = explode("=",$str);
                $res[$temp_arr[0]] = $temp_arr[1];
                unset($argvs[$key]);
            }
        }
        return $res;
    }
}

if(!function_exists("deal_map")){
    function deal_map($data,$col_arr,$set,$event_name,&$res){

        if(isset($data["pv"])){
            $set[$event_name."_pv"] = $data["pv"];
            $set[$event_name."_uv"] = $data["uv"];
            $table_name = getTableName($event_name);
            $sql = map_to_sql($set,$table_name);
            $res[] = $sql;
            return;
        }

        foreach($data as $key=>$v){

            $arr = $col_arr;
            $col1 = array_shift($arr);
            $set[$col1] = $key;
            deal_map($v,$arr,$set,$event_name,$res);
        }
    }
}
if(!function_exists("output_to_db")){
    function output_to_db($data,$output_config){
        global $delimiter_arr;
        global $table_pre;
        $arr = $delimiter_arr;
        $arr[] = "date";
        auto_create_db_data($data,$arr,$table_pre,$output_config);
    }
}

function map_to_sql($jsonArr,$table){

    $key_arr = [];
    $v_arr = [];
    foreach($jsonArr as $key => $v){
        $key_arr[] = "`".$key."`";
        $v_arr[] = "'".$v."'";
    }
    $key = join(',', $key_arr);
    $v = join(',', $v_arr);
    $sql = "REPLACE INTO $table ($key) VALUES ($v);";
    return $sql;
}


function auto_create_db_data($data,$col_arr,$table_pre,$config){

    $num = 1;
    $inputFailNum = 0;
    $mySql = new mysqli($config["ip_host"], $config["ip_user"], $config["ip_pwd"], "", $config["ip_port"]);
    if (false == $mySql) {
        echo "������ݿ�ʧ�ܣ�\n";
        die("Connection failed: " . $mySql->connect_error);
    }
    $mySql->query("SET NAMES UTF8");
    if (!$mySql->select_db($config["ip_database"])) {
        die("Uh oh, couldn't select database");
    }
    $set = array();
    $sql_map = array();
    crate_table($mySql,$table_pre);
    deal_map($data,$col_arr,$set,$table_pre, $sql_map);
    $mySql->query('start transaction');
    $mySql->autocommit(FALSE);
    foreach($sql_map as $sql){
        if($mySql ->query($sql) === false){
            $inputFailNum++;
            $error = $mySql ->errno;
            echo $sql . " :run sql fail! mysql error is $error\n";
        }
    }
    $mySql->commit();
}

function getTableName($table_pre){
    global $delimiter_arr;
    $table = $table_pre;
    foreach($delimiter_arr as $v){
        $table.="_".$v;
    }
    return substr($table."_pv_uv",0,64);
}
function crate_table($mySql,$table_pre){
    global $delimiter_arr;
    global $value_map;
    $table_name = getTableName($table_pre);
    $result = $mySql->query("SHOW TABLES LIKE '".$table_name."';");
    $row = $result->fetch_object();
    if(empty($row)){
        $middle = "";
        $pv = $table_pre."_pv";
        $uv = $table_pre."_uv";
        $primary = "";
        foreach($delimiter_arr as $v){
            $type = $value_map[$v];
            $middle .= "`$v` $type NOT NULL,";
            $primary .= "`$v`,";
        }
        $create_sql = <<<EOD
CREATE TABLE `$table_name` (
$middle
`$pv` int(11) NOT NULL,
`$uv` int(11) NOT NULL,
`date` varchar(20) NOT NULL,
PRIMARY KEY ($primary`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOD;
        echo $create_sql."\n";
        if(false == $mySql->query($create_sql))
        {
            $error = $mySql->errno;;
            echo "create table fail error is $error \n";
        }
    }
}
