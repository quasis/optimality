<?php

namespace optimality;

class Section extends Term {

    const UNBASE = 'section_unbase';
    const SEMETA = 'section_semeta';
    const SENAME = 'section_sename';
    const SEDESC = 'section_sedesc';
    const SMMETA = 'section_smmeta';
    const SMNAME = 'section_smname';
    const SMDESC = 'section_smdesc';


    function __construct($object) {
        parent::__construct($object);
    }


    function __invoke($target, $option) {

        if (isset($option[static::UNBASE])) {

            if (strpos($target, $this->route) !== 0) {
                return static::route($target, NULL);
            }
        }

        return parent::__invoke($target, $option);
    }


    static function route($origin, $termid, $prefix = 'category') {
        return ($prefix !== 'category') ? $origin : str_replace('/' .
            (get_option('category_base') ?: $prefix) . '/', '/', $origin);
    }


    static function query($fields) {

        if ($handle = @$fields['attachment'] ?: @$fields['name']) {

            $object = get_term_by('slug', $handle, 'category');

            if (is_object($object) && !is_wp_error($object)) {

                unset($fields['name'], $fields['attachment']);
                return ['category_name' => $handle] + $fields;
            }
        }

        return $fields;
    }


    static function fetch() {
        return static::encap(get_terms(['taxonomy' => 'category']));
    }
}

?>
