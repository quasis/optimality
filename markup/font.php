<?php

namespace optimality;

class Font {

    const FORMAT = 'text/css';

    public $file;


    function __construct($source = []) {
        $this->file = $source;
    }


    function import($source) {
        $this->file[$source] = true;
    }


    function build($cdnurl) {

        $family = [];
        $subset = [];

        foreach ($this->file as $source => $binary) {

            if ($source = wp_parse_url($source, PHP_URL_QUERY)) {

                parse_str($source, $fields);

                if (isset($fields['family'])) {
                    $family[$fields['family']] = true;
                }

                if (isset($fields['subset'])) {
                    $subset += array_fill_keys(explode(',', $fields['subset']), 1);
                }
            }
        }

        return $cdnurl . '?' . http_build_query([

            'family' => implode('|', array_keys($family)),
            'subset' => implode(',', array_keys($subset)),

        ], NULL, '&', PHP_QUERY_RFC1738);
    }
}

?>
