<?php

namespace Bethowen\Helpers;

//select data - this we select url and names

class CacheEng {
    public $memCache;
    public $name_claster;

    public function __construct($story_baners = null) {
        if($story_baners == null) {
            $this->memCache = new \Memcached();
        } else {
            $this->memCache = new \Memcached($story_baners);
        }
        $this->memCache->addServer('127.0.0.1', 11211);
        $this->name_claster = $story_baners;
    }

    public function getData($key) {
        return $this->memCache->get($key);
    }

    public function setData($key, $value) {
        $this->memCache->addByKey($this->name_claster, $key, $value, time() + (60 * 60 * 24 * LONG_STORE_IN_DAYS));
        //this we update if case key some reason exist
        if($this->memCache->getResultCode() == \Memcached::RES_NOTSTORED) {
            $this->upDateSpecKey($key, $value);
        }
    }

    public function deleteData($key) {
        $this->memCache->deleteByKey($this->name_claster, $key);
    }

    public function setDataTimeout($key, $value, $timeout) {
        $this->memCache->addByKey($this->name_claster, $key, $value, time() + $timeout);
        //this we update if case key some reason exist
        if($this->memCache->getResultCode() == \Memcached::RES_NOTSTORED) {
            $this->upDateSpecKeyTimeout($key, $value, $timeout);
        }
    }

    public function getKeys() {
        return $this->memCache->getAllKeys();
    }

    public function upDateSpecKey($key, $value) {
        $this->memCache->replaceByKey($this->name_claster, $key, $value, time() + (60 * 60 * 24 * LONG_STORE_IN_DAYS));
    }

    public function upDateSpecKeyTimeout($key, $value, $timeout) {
        $this->memCache->replaceByKey($this->name_claster, $key, $value, time() + $timeout);
    }

    public function close() {
        $this->memCache->quit();
    }

	public function __destruct() {
		$this->memCache->quit();
    }
}