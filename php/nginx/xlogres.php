<?php
/**
 * Created by PhpStorm.
 * User: chengxueming
 * Date: 17-6-22
 * Time: 上午11:22
 */
class xlogres {
    var $content;
    var $valuemap;

    public function getPVUVMap() {
        $content = $this->content;
        $this->calculate_uv($content);
        return $content;
    }

    public function calculate_uv(&$content){
        if(isset($content["users"])){
            $content["uv"] = count($content["users"]);

            unset($content["users"]);
            return;
        }

        foreach($content as &$v){

            $this->calculate_uv($v);
        }
    }

    public  function result_to_file($file)
    {
        $arr = array();
        if(file_exists($file)){
            $arr = json_decode(file_get_contents($file), true);
            if(empty($arr)){
                $arr = array();
            }
        }
        $arr = $this->m_array_merge($arr, $this->content);
        print_r(array_keys($arr));
        $file_h = fopen($file, "w");
        $content_json = json_encode($arr, JSON_FORCE_OBJECT);
        fwrite($file_h, $content_json);
        fclose($file_h);
    }

    private function m_array_merge($arr,$arr1){
        if(empty($arr)){
            return $arr1;
        }else if(empty($arr1)){
            return $arr;
        }
        if($this->is_date_arr($arr)){
            if(!$this->is_date_arr($arr1)){
                exit("wrong data");
            }
            echo "hahahhhhhhhhhhhhhhhhhhhhhh"."\n";
            return array_replace($arr,$arr1);
        }
        $k1 = array_keys($arr);
        $k2 = array_keys($arr1);
        $ks = array_merge($k1,$k2);
        $res = array();
        foreach($ks as $key){
            $res[$key] = $this->m_array_merge($arr[$key],$arr1[$key]);
        }
        return $res;
    }

    private static function is_date_arr($arr){
        $keys = array_keys($arr);
        //\d{4}-\d{2}-\d{2}
        return date("Y-m-d", strtotime($keys[0])) !== "1970-01-01";
    }

    public static  function get_pv_uv(&$arr,$uuid){
        if(!isset($arr["pv"])){
            $arr["pv"] = 1;
        }else{
            $arr["pv"] ++;
        }
        if(!isset($arr["users"])){
            $arr["users"] = array();
        }
        if(!isset($arr["users"][$uuid])){
            $arr["users"][$uuid] = 1;
        }else{
            $arr["users"][$uuid] += 1;
        }
    }

    public  function getValueMap($v,$key,&$res)
    {
        if(is_numeric($v))
        {
            if(strpos($v,".") === false)
            {
                $res[$key] = "int(11) DEFAULT 0";
            }else{
                $res[$key] = "float DEFAULT 0";
            }

        }else
        {
            $res[$key] = "varchar(32) DEFAULT ''";
        }
    }
    function  adduuid($temp_argv, $jsonArrRemark, $date) {
            $flag = 0;
            if(count($temp_argv) > 0){
                $contents = &$this->content[$jsonArrRemark[$temp_argv[0]]];
                if(isset($jsonArrRemark[$temp_argv[0]]))
                {
                    $this->getValueMap($jsonArrRemark[$temp_argv[0]],$temp_argv[0],$this->valuemap);
                }
                $flag = 1;
                unset($temp_argv[0]);
                foreach($temp_argv as $arg){
                    $contents = &$contents[$jsonArrRemark[$arg]];
                    if(count($this->valuemap) < count($temp_argv) && isset($jsonArrRemark[$arg]))
                    {
                        $this->getValueMap($jsonArrRemark[$arg],$arg,$value_map);
                    }
                    if(!isset($jsonArrRemark[$arg]))
                    {
                         return false;
                    }
                }
            }
            if($flag){
                $contents = &$contents[$date];
            }else{
                $contents = &$this->content[$date];
            }
            if(isset($jsonArrRemark["uuid"])){
                xlogres::get_pv_uv($contents, $jsonArrRemark["uuid"]);
            }else{
                return false;
            }
            return true;
       }

}