<?php

namespace optimality;

class Style {

    const MINIFY = 'style_minify';
    const CDNLIB = 'style_cdnlib';
    const FORMAT = 'text/css';

    public $code;

    static $filter = [

        '/wp-admin/',
        '/admin-bar.min.css',
        '/dashicons.min.css',
        '/customize-preview.min.css',
        '.widget-customizer-highlighted-widget',
        '.wp-customizer-unloading',
        '#wpadminbar',
    ];

    static $hoster = [

        'animate'         => 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/%s/animate.min.css',
        'bootstrap'       => 'https://maxcdn.bootstrapcdn.com/bootstrap/%s/css/bootstrap.min.css',
        'font-awesome'    => 'https://maxcdn.bootstrapcdn.com/font-awesome/%s/css/font-awesome.min.css',
        'materialize'     => 'https://cdnjs.cloudflare.com/ajax/libs/materialize/%s/css/materialize.min.css',
        'mediaelement'    => 'https://cdnjs.cloudflare.com/ajax/libs/mediaelement/%s/mediaelementplayer.min.css',
        'normalize'       => 'https://cdnjs.cloudflare.com/ajax/libs/normalize/%s/normalize.min.css',
        'skeleton'        => 'https://cdnjs.cloudflare.com/ajax/libs/skeleton/%s/skeleton.min.css',
    ];


    function __construct($source = []) {
        $this->code = $source;
    }


    function append($source, $render = 'all') {
        $this->code[] = ($render === 'all') ? $source :
            "@media {$render}{{$source}}";
    }


    function import($source, $render = 'all') {
        $this->code[] = "@import url(\"$source\") {$render};";
    }


    function valid($source) {

        foreach (static::$filter as $search) {

            if (strpos($source, $search) !== false) {
                return false;
            }
        }

        return true;
    }


    function cache($cdnurl) {

        $handle = sprintf('~%s.css', md5(serialize($this->code)));

        if (!file_exists($target = __CDNDIR__ . $handle)) {

            file_put_contents($target , $string = $this->build($target));
            file_put_contents($target . '.gz', gzencode($string, 9));
        }

        return ($cdnurl ?: __CDNURL__) . $handle;
    }


    function build($target) {

        $quotes = '"(?:[^"\\\]++|\\\.)*+"';
        $aposts = "'(?:[^'\\\]++|\\\.)*+'";
        $remark = '\/\* (?> .*? \*\/ )';
        $scopes = '\s*+ ; \s*+ ( } ) \s*+';
        $operat = '\s*+ ( [*$~^|]?+= | [{};,>~+-] | !important\b ) \s*+';
        $lbrace = '( [[(:] ) \s++';
        $rbrace = '\s++ ( [])] )';
        $colons = "\s++ ( : ) \s*+ (?!(?> [^{}\"']++ |{$quotes}|{$aposts})*+ { )";
        $spaces = '^\s++|\s++\z|(\s)\s+';
        $regexp = "($quotes|$aposts)|$scopes|$operat|$lbrace|$rbrace|$colons|$spaces";

        $string = preg_replace("/($quotes|$aposts)|$remark/sx", '$1', $this->bundle($target));
        return preg_replace("/$regexp/six", '$1$2$3$4$5$6$7', $string);
    }


    private function bundle($target) {

        $stream = stream_context_create([
            'ssl' => array('verify_peer' => false, 'verify_peer_name' => false),
        ]);  

        $import = '@import \s* url \( [\'"]? ([^\'"\)]+) [\'"]? \) \s* ([^;]*) \s* ;';

        return preg_replace_callback("/$import/ix", function($result) use($stream) {

            if ($string = @file_get_contents($source = $result[1], false, $stream)) {

                $string = (new static([$string]))->bundle($source);

                return (($render = @$result[2] ?: 'all') == 'all') ?
                    $string : "@media {$render}\n{\n$string\n}";
            }

        }, $this->unfold($target) ?: '');
    }


    private function unfold($target) {

        $regexp = 'url \( [\'"]? ([^\'"\)]+) [\'"]? \)';
        $target = dirname($target);

        return preg_replace_callback("/$regexp/ix", function($result) use($target) {

            if (filter_var($source = $result[1], FILTER_VALIDATE_URL)) {
                return "url('{$source}')";
            }

            return (strpos($source, 'data:') === 0) ? "url('{$source}')" :
                "url('{$target}/{$source}')";

        }, implode("\n", $this->code));
    }


    static function serve($source, $handle) {

        if (($target = @static::$hoster[$handle]) && ($offset = strpos($source, '?ver='))) {
            return sprintf($target, substr($source, $offset + 5));
        }

        return $source;
    }


    // ACTIONS

    static function countCache() {
        return count(glob(__CDNDIR__ . '~*.{css}', GLOB_BRACE));
    }


    static function cleanCache() {
        return count(array_filter(glob(__CDNDIR__ . '~*.{css,css.gz}', GLOB_BRACE), 'unlink')) / 2;
    }
}

?>