<?php

namespace optimality;

class Script
{
    const MINIFY = 'script_minify';
    const CDNLIB = 'script_cdnlib';
    const FORMAT = 'text/javascript';

    public $code;

    static $filter =
    [
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

    static $export =
    [
        'jquery-core'    => 'https://cdnjs.cloudflare.com/ajax/libs/jquery/%s/jquery.min.js',
        'jquery-migrate' => 'https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/%s/jquery-migrate.min.js',
        'mediaelement'   => 'https://cdnjs.cloudflare.com/ajax/libs/mediaelement/%s/mediaelement-and-player.min.js',
        'device'         => 'https://cdnjs.cloudflare.com/ajax/libs/device.js/0.2.7/device.min.js',
        'bootstrap'      => 'https://maxcdn.bootstrapcdn.com/bootstrap/%s/js/bootstrap.min.js',
    ];


    function __construct($source = [])
    {
        $this->code = $source;
    }


    function append($source)
    {
        $this->code[] = $source;
    }


    function import($source)
    {
        $this->code[] = "import \"$source\";";
    }


    function valid($source)
    {
        foreach (static::$filter as $search)
        {
            if (strpos($source, $search) !== false)
            {
                return false;
            }
        }

        return true;
    }


    function cache($cdnurl)
    {
        $handle = sprintf('~%s.js', md5(serialize($this->code)));

        if (!file_exists($target = __CDNDIR__ . $handle))
        {
            file_put_contents($target , $string = $this->build($target));
            file_put_contents($target . '.gz', gzencode($string, 9));
        }

        return ($cdnurl ?: __CDNURL__) . $handle;
    }


    function build($target)
    {
        require_once(__DIR__ . '/../vendor/jsmin.php');
        return \PHPWee\JSMin::minify($this->bundle($target));
    }


    private function bundle($target)
    {
        $import = 'import \s* " ([^"]+) " \s* ;';

        return preg_replace_callback("/$import/ix", function($result)
        {
            if ($string = @file_get_contents($source = $result[1]))
            {
                return (new static([$string]))->bundle($source);
            }

        }, implode("\n", $this->code));
    }


    static function serve($source, $handle)
    {
        if (($target = @static::$export[$handle]) && ($offset = strpos($source, '?ver=')))
        {
            return sprintf($target, substr($source, $offset + 5));
        }

        return $source;
    }


    // ACTIONS

    static function countCache()
    {
        return count(glob(__CDNDIR__ . '~*.{js}', GLOB_BRACE));
    }


    static function cleanCache()
    {
        return count(array_filter(glob(__CDNDIR__ . '~*.{js,js.gz}', GLOB_BRACE), 'unlink')) / 2;
    }
}

?>