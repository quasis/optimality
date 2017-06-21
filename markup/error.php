<?php

namespace optimality;

class Error extends Html {

    const PARENT = 'error_parent';


    function __construct($object) {
        parent::__construct($object);
    }


    function __invoke($target, $option) {

        if (isset($option[static::PARENT])) {
            return static::route($target);
        }

        return parent::__invoke($target, $option);
    }


    static function route($origin) {

        $origin = wp_parse_url($origin) ?: [];

        if (!isset($origin['query']) && isset($origin['path'])) {
            $origin['path'] = dirname($origin['path']) . '/';
        }

        return @$origin['scheme'] . '://' . @$origin['host'] . @$origin['path'];
    }
}

?>
