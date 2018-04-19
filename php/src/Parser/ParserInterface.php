<?php
/**
 * @author cheng.xueming
 * @since  2018-04-19
 */

namespace App\Parser;

abstract class ParserInterface implements \Iterator {

    var $file_handler = null;

    public function loadFile($filename) {
        $this->file_handler = gzopen($filename);
    }

    abstract function extract();

    /**
     * @inheritdoc
     */
    public function current() {

    }

    /**
     * @inheritdoc
     */
    public function next() {

    }

    /**
     * @inheritdoc
     */
    public function key() {

    }

    /**
     * @inheritdoc
     */
    public function valid() {

    }

    /**
     * @inheritdoc
     */
    public function rewind() {

    }
}