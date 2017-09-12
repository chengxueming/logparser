<?php
/**
 * 命令行参数解析工具类
 * @author guolinchao
 */
class CommandLine
{
    // 临时记录短选项的选项值
    private static $shortOptVal = null;
    // options value
    private static $optsArr = array();
    // command args
    private static $argsArr = array();
    // 是否已解析过命令行参数
    private static $isParse = false;

    public function construct() {
        if(!self::$isParse) {
            self::parseArgs();
        }
    }

    /**
     * 获取选项值
     */
    public function getOptVal($opt) {
        if(isset(self::$optsArr[$opt])) {
            return self::$optsArr[$opt];
        }
        return null;
    }

    /**
     * 获取命令行参数
     */
    public function getArg($index) {
        if(isset(self::$argsArr[$index])) {
            return self::$argsArr[$index];
        }
        return null;
    }

    /**
     * 注册选项对应的回调函数, $callback 应该有一个参数, 用于接收选项值
     */
    public function option($opt, $callback) {
        // check
        if(!is_callable($callback)) {
            throw new Exception(sprintf('Not a valid callback function [%s].', $callback));
        }
        if(isset(self::$optsArr[$opt])) {
            // call user function
            call_user_func($callback, self::$optsArr[$opt]);
        } else {
            throw new Exception(sprintf('Unknown option [%s].', $opt));
        }
    }

    /**
     * 是否是 -s 形式的短选项
     */
    public static function isShortOptions($opt) {
        if(preg_match('/^\-([a-zA-Z])$/', $opt, $matchs)) {
            return $matchs[1];
        }
        return false;
    }

    /**
     * 是否是 -hlocalhost 形式的短选项
     */
    public static function isShortOptionsWithValue($opt) {
        if(preg_match('/^\-([a-zA-Z])([\S]+)$/', $opt, $matchs)) {
            self::$shortOptVal = $matchs[2];
            return $matchs[1];
        }
        return false;
    }

    /**
     * 是否是 --help 形式的长选项
     */
    public static function isLongOptions($opt) {
        if(preg_match('/^\-\-([a-zA-Z0-9\-_]{2,})$/', $opt, $matchs)) {
            return $matchs[1];
        }
        return false;
    }

    /**
     * 是否是 --options=opt_value 形式的长选项
     */
    public static function isLongOptionsWithValue($opt) {
        if(preg_match('/^\-\-([a-zA-Z0-9\-_]{2,})(?:\=(.*?))$/', $opt, $matchs)) {
            $tmpV = trim($matchs[2], '"');
            self::$shortOptVal = empty($tmpV) ? true : $tmpV;
            return $matchs[1];
        }
        return false;
    }

    /**
     * 是否是命令行参数
     */
    public static function isArg($value) {
        return ! preg_match('/^\-/', $value);
    }

    /**
     * 解析命令行参数
     */
    public static function parseArgs() {
        global $argv;

        if(self::$isParse) {
            return ;
        }

        // index start from 1.
        $index = 1;
        $length = count($argv);
        $retArgs = array('opts'=>array(), 'args'=>array());

        while($index < $length) {
            // current value
            $curVal = $argv[$index];
            // short options or long options
            if( ($sp = self::isShortOptions($curVal)) || ($lp = self::isLongOptions($curVal)) ) {
                // options array key
                $key = $sp ? $sp : $lp;
                // go ahead
                $index++;
                if( isset($argv[$index]) && self::isArg($argv[$index]) ) {
                    $retArgs['opts'][$key] = $argv[$index];
                } else {
                    $retArgs['opts'][$key] = true;
                    // back away
                    $index--;
                }
            } // short options with value || long options with value
            else if( false !== ($key = self::isShortOptionsWithValue($curVal))
                || false !== ($key = self::isLongOptionsWithValue($curVal)) ) {
                $retArgs['opts'][$key] = self::$shortOptVal;
            } // command args
            else if( self::isArg($curVal) ) {
                $retArgs['args'][] = $curVal;
            }
            // incr index
            $index++;
        }

        self::$optsArr = $retArgs['opts'];
        self::$argsArr = $retArgs['args'];
        self::$isParse = true;

        return $retArgs;
    }
}