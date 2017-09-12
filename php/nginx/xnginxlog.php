<?php
/**
 * Created by PhpStorm.
 * User: chengxueming
 * Date: 17-6-22
 * Time: 上午11:20
 */
require "xlogres.php";

class xnginxlog  {
    var $date_format;
    var $delimiter_arr;
    var $domain;
    var $res;
    var $path;
    var $max_depth;
    var $date_end;
    var $source_path;
    var $url_pre;
    var $dir;

    var $table_pre;

    function __construct($url_pre, $date_format, $delimiter_arr, $domain, $path,$date_end,  $max_depth , $collection_path , $source_path = "")
    {
        $this->date_format = $date_format;
        $this->delimiter_arr = $delimiter_arr;
        $this->dir = $collection_path;
        $this->url_pre = $url_pre;
        $this->domain = $domain;
        $this->path = $path;
        $this->max_depth  = $max_depth;
        $this->source_path = $source_path;
        $this->date_end = $date_end;
        $this->res = new xlogres();
    }

    public function runSpaceLess() {
        $filename = $this->getFileName($this->url_pre);
        for($i = 0; $i < $this->max_depth; $i++) {
            $date = date("Ymd", strtotime("-$i day $this->date_end"));
            $this->create_file($date, $filename);
            $this->deal_file($filename);
        }
        $this->res->result_to_file($filename.".out");
        $this->table_pre = $this->getTablepre($this->url_pre);
    }

    public function runSpaceMore($type = "normal") {
        $filename = $this->getFileName($this->url_pre);
        //$filename = "D:\\gitlab\\crontab\\event_shell\\log_task_c_list";
        if($type == "rewrite") {
            for($i = 0; $i < $this->max_depth; $i++) {
                $date = date("Ymd", strtotime("-$i day $this->date_end"));
                $this->create_file($date, $filename, ">>");
            }
        }
        $this->deal_file($filename);
        $this->res->result_to_file($filename.".out");
        $this->table_pre = $this->getTablepre($this->url_pre);
    }


    private function getFileName($urlpre)
    {
        $filename = $this->getTablepre($urlpre);
        return $this->dir.$filename;
    }

    function getTablepre($urlpre)
    {
        return "log_".str_replace("/","_",$urlpre);;
    }

    public function  deal_file($filename)
    {
        echo "begin analyse file\n";
        $begin = time();
        $continue_line = 0;
        $uuid_empty_line = 0;
        $arg_empty_line = 0;
        $handle = @fopen($filename, "r");
        if ($handle) {
            while (($line = fgets($handle, 4096)) !== false) {
                //获取url中的每一个参数
                $url = urldecode(explode(" ", $this->getFirstPregStr("\"","\"",$line))[1]);
                $jsonArrRemark = $this->getParamsFromUrl($url);
                if(empty($jsonArrRemark)){
                    $continue_line ++;
                    //echo "$line param is null\n";
                    continue;
                }
                //获取当前日期
                $time =  explode(" ", $this->getFirstPregStr("\[","\]",$line))[0];
                $date = substr($time,0,11);
                if(count(explode("/",$date)) < 3){
                    continue;
                }
                $date = DateTime::createFromFormat('j/M/Y',$date);
                $date = $date->format($this->date_format);
                //数据分类
                $this->res->adduuid($this->delimiter_arr, $jsonArrRemark, $date);
            }

            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }
        echo "analyse file cost time:".(time() - $begin)."second continue line is $continue_line; uuid empty line is $uuid_empty_line;arg emtpty line $arg_empty_line\n";
    }

    private   function getFirstPregStr($begin,$end,$line)
    {
        $matches = array();
        preg_match("/$begin.*?$end/", $line, $matches);
        return substr($matches[0],1,strlen($matches[0]) -2);
    }

    private   function getParamsFromUrl($url)
    {
        $param_url = substr($url,strpos($url,"?")+1,strlen($url));
        $params = explode("&", $param_url);
        $res = array();
        $flag = 0;
        foreach($params as $param)
        {
            $v = explode("=",$param);
            if(count($v) < 2)
            {
                $flag = 1;
                continue;
            }
            $res[$v[0]] = $v[1];
        }
        return $res;
    }

    function create_file($date, $file_name, $type=">")
    {
        $path = $this->path.$this->domain."/";
        $log_date = date("Ymd",strtotime("+1 day $date"));
        if(!empty($this->source_path))
        {
            $cmd = "./help_gunzip.sh $this->domain $this->source_path $this->path $log_date";
            echo $cmd."\n";
            shell_exec($cmd);
        }
        $cmd = "find $path|grep $this->domain"."_access.log-$log_date|xargs grep $this->url_pre $type $file_name";
        echo $cmd."\n";
        shell_exec($cmd);
    }

}