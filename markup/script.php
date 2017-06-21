<?php

namespace optimality;

class Script {

    const MINIFY = 'script_minify';
    const CDNLIB = 'script_cdnlib';
    const FORMAT = 'text/javascript';

    public $code;

    static $filter = [

        'nonce',
        'user_login',
        '"disqusConfig"',
        '/admin-bar.min.js',
        '/customize-preview.min.js',
        '/froogaloop.min.js',
        '_wpCustomizePreviewNavMenusExports',
        '_wpmejsSettings',
        '_wpUtilSettings',
        '_wpWidgetCustomizerPreviewSettings',
        '_customizePartialRefreshExports',
        'theme_directory_uri',
        '/^customize_messenger_channel=/',
        'The customizer requires postMessage and CORS',
        'adsbygoogle',
    ];

    static $hoster = [

        'backbone'       => 'https://cdnjs.cloudflare.com/ajax/libs/backbone.js/%s/backbone-min.js',
        'bootstrap'      => 'https://maxcdn.bootstrapcdn.com/bootstrap/%s/js/bootstrap.min.js',
        'device'         => 'https://cdnjs.cloudflare.com/ajax/libs/device.js/0.2.7/device.min.js',
        'hoverIntent'    => 'https://cdnjs.cloudflare.com/ajax/libs/jquery.hoverintent/%s/jquery.hoverIntent.min.js',
        'jquery-color'   => 'https://cdnjs.cloudflare.com/ajax/libs/jquery-color/%s/jquery.color.min.js',
        'jquery-core'    => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/%s/jquery.min.js',
        'jquery-form'    => 'https://cdnjs.cloudflare.com/ajax/libs/jquery.form/%s/jquery.form.min.js',
        'jquery-migrate' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/%s/jquery-migrate.min.js',
        'masonry'        => 'https://cdnjs.cloudflare.com/ajax/libs/masonry/%s/masonry.pkgd.min.js',
        'mediaelement'   => 'https://cdnjs.cloudflare.com/ajax/libs/mediaelement/%s/mediaelement-and-player.min.js',
        'schedule'       => 'https://cdnjs.cloudflare.com/ajax/libs/schedulejs/%s/schedule.min.js',
        'swfobject'      => 'https://cdnjs.cloudflare.com/ajax/libs/swfobject/%s/swfobject.min.js',
        'tiny_mce'       => 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/%s/tinymce.min.js',
        'underscore'     => 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/%s/underscore-min.js',
    ];


    function __construct($source = []) {
        $this->code = $source;
    }


    function append($source) {
        $this->code[] = $source;
    }


    function import($source) {
        $this->code[] = "import \"$source\";";
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

        $handle = sprintf('~%s.js', md5(serialize($this->code)));

        if (!file_exists($target = __CDNDIR__ . $handle)) {

            file_put_contents($target , $string = $this->build($target));
            file_put_contents($target . '.gz', gzencode($string, 9));
        }

        return ($cdnurl ?: __CDNURL__) . $handle;
    }


    function build($target) {

        require_once(__DIR__ . '/../vendor/jsmin.php');
        return \PHPWee\JSMin::minify($this->bundle($target));
    }


    private function bundle($target) {

        $stream = stream_context_create([
            'ssl' => array('verify_peer' => false, 'verify_peer_name' => false),
        ]);  

        $import = 'import \s* " ([^"]+) " \s* ;';

        return preg_replace_callback("/$import/ix", function($result) use($stream) {

            if ($string = @file_get_contents($source = $result[1], false, $stream)) {
                return (new static([$string]))->bundle($source);
            }

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
        return count(glob(__CDNDIR__ . '~*.{js}', GLOB_BRACE));
    }


    static function cleanCache() {
        return count(array_filter(glob(__CDNDIR__ . '~*.{js,js.gz}', GLOB_BRACE), 'unlink')) / 2;
    }
}

?>