<?php

namespace optimality;

class Image {

    const MINIFY = 'image_minify';
    const SRCSET = 'image_srcset';

    public $slug;
    public $type;
    public $hash;
    public $ratio;
    public $width;
    public $height;
    static $cache = [];


    function __construct($source) {

        $source = substr(sprintf('%s://%s%s', __SCHEME__, __DOMAIN__,
            wp_parse_url($source, PHP_URL_PATH)), strlen(__CDNURL__));

        $this->type = pathinfo($source, PATHINFO_EXTENSION);

        if (preg_match("/\-(\d+)x(\d+)\.{$this->type}$/", $source, $dimens)) {

            list($this->width, $this->height) = array_slice($dimens, 1);
            $this->slug = substr($source, 0, -strlen("-{$this->width}x{$this->height}.{$this->type}"));
        }
        else {

            list($this->width, $this->height) = getimagesize(__CDNDIR__ . $source);
            $this->slug = substr($source, 0, -strlen(".{$this->type}"));
        }

        $this->ratio = number_format($this->width / $this->height, 1);
        $this->hash  = sprintf('%s-%s.%s', $this->slug, $this->ratio, $this->type);
    }


    function sizes($cdnurl) {

        if (isset(static::$cache[$this->hash])) {
            return static::$cache[$this->hash];
        }

        $handle = __CDNDIR__ . $this->slug;
        $srcset = ["{$cdnurl}{$this->slug}.{$this->type}"];

        foreach (glob("{$handle}-[0-9]*x[0-9]*.{$this->type}") as $source) {

            if (preg_match("/\-(\d+)x(\d+)\.{$this->type}$/", $source, $dimens) &&
               (number_format($dimens[ 1 ] / $dimens[ 2 ], 1) === $this->ratio)) {

                $source = $cdnurl . substr( $source, strlen( __CDNDIR__ ) );
                $srcset[$width = intval($dimens[1])] = "{$source} {$width}w";
            }
        }

        ksort($srcset); return static::$cache[$this->hash] = $srcset;
    }


    static function mount($editor) {

        require_once(__DIR__ . '/../editor/im.php');
        array_unshift($editor, IM::class);
        return $editor;
    }


    // ACTIONS

    static function countCache() {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->posts}` WHERE post_type='attachment' AND post_mime_type LIKE 'image/%'");
    }


    static function fetchCache() {
        return $GLOBALS['wpdb']->get_results("SELECT ID FROM `{$GLOBALS['wpdb']->posts}` WHERE post_type='attachment' AND post_mime_type LIKE 'image/%'");
    }


    static function buildCache($params) {

        add_filter('wp_image_editors', [ static::class, 'mount' ], 100);

        $images = isset( $params['ids'] ) ? explode(',', $params['ids']) :
            array_map(function($o) {return $o->ID;}, static::fetchCache());

        $result = 0; set_time_limit(max(120 * count($images), 30));

        foreach ($images as $offset => $object) {

            if (!file_exists($source = get_attached_file($object))) {
                continue;
            }

            $record = wp_generate_attachment_metadata($object, $source);

            if (!empty($record) && !is_wp_error($record) && (++$result)) {
                wp_update_attachment_metadata($object, $record);
            }
        }

        return $result;
    }
}

?>
