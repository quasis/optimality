<?php

namespace optimality;

class Site extends Html {

    const DBTEMP = 'site_dbtemp';
    const SEMETA = 'site_semeta';
    const SENAME = 'site_sename';
    const SEDESC = 'site_sedesc';
    const SMMETA = 'site_smmeta';
    const SMNAME = 'site_smname';
    const SMDESC = 'site_smdesc';

    public $lang;
    public $goog;
    public $face;
    public $twit;


    function __construct($object = NULL) {

        parent::__construct($object);

        $this->ruid  = NULL;
        $this->name  = get_bloginfo('name');
        $this->lead  = get_bloginfo('description');
        $this->image = get_option('image');
        $this->route = home_url('/');
        $this->lang  = get_locale();
        $this->goog  = get_option('googleplus');
        $this->face  = get_option('facebook');
        $this->twit  = get_option('twitter');
        $this->site  = $this;
    }


    function getJson($option) {

        return array_merge(parent::getJson($option), [
            '@type'     => 'WebSite',
            'publisher' => $this->goog,
        ]);
    }


    static function fetch() {
        return static::encap([ NULL ]);
    }


    static function countTemp() {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->options}` value, `{$GLOBALS['wpdb']->options}` timer WHERE timer.option_name LIKE '%_transient_timeout_%' AND value.option_name = REPLACE(timer.option_name, '_transient_timeout_', '_transient_') AND timer.option_value < UNIX_TIMESTAMP()");
    }


    static function cleanTemp() {
        return $GLOBALS['wpdb']->query("DELETE value, timer FROM `{$GLOBALS['wpdb']->options}` value, `{$GLOBALS['wpdb']->options}` timer WHERE timer.option_name LIKE '%_transient_timeout_%' AND value.option_name = REPLACE(timer.option_name, '_transient_timeout_', '_transient_') AND timer.option_value < UNIX_TIMESTAMP()");
    }
}

?>
