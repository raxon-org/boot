<?php
namespace Package\Raxon\Boot\Trait;

use Raxon\Config;

trait Main {

    public function init($flags, $options): void
    {
        breakpoint($flags);
        breakpoint($options);
        $object = $this->object();
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        d($object->request());
    }
}