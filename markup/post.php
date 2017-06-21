<?php

namespace optimality;

class Post extends Page {

    const DBAUTO = 'post_dbauto';
    const DBEDIT = 'post_dbedit';
    const DBMETA = 'post_dbmeta';
    const SEMETA = 'post_semeta';
    const SENAME = 'post_sename';
    const SEDESC = 'post_sedesc';
    const SMMETA = 'post_smmeta';
    const SMNAME = 'post_smname';
    const SMDESC = 'post_smdesc';

    public $section;


    function __construct($object) {
        parent::__construct($object);
    }


    function __invoke($target, $option) {

        if (count($object = get_the_terms($this->proto, 'category'))) {
            $this->section = $object[0]->name;
        }

        return parent::__invoke($target, $option);
    }


    function getMeta($option) {

        return array_merge(parent::getMeta($option), [

            'article:section' => $this->section,
            'article:tag'     => array_map(function($object) {
                return $object->name;

            }, get_the_terms($this->proto, 'post_tag') ?: []),
        ]);
    }


    function getJson($option)
    {
        return array_merge(parent::getJson($option), [

            '@type'           => 'Article',
            'articleSection'  => $this->section,
            'keywords'        => implode(',', array_map(function($object) {
                return $object->name;

            }, get_the_terms($this->proto, 'post_tag') ?: [])),
        ]);
    }


    static function fetch() {
        return static::encap(get_posts(['numberposts' => -1, 'post_type' => 'post']));
    }


    static function countAuto() {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->posts}` WHERE post_status='auto-draft'");
    }


    static function cleanAuto() {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->posts}` WHERE post_status='auto-draft'");
    }


    static function countEdit() {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->posts}` WHERE post_type='revision'");
    }


    static function cleanEdit() {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->posts}` WHERE post_type='revision'");
    }


    static function countMeta() {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->postmeta}` WHERE post_id NOT IN (SELECT ID FROM `{$GLOBALS['wpdb']->posts}`)");
    }


    static function cleanMeta() {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->postmeta}` WHERE post_id NOT IN (SELECT ID FROM `{$GLOBALS['wpdb']->posts}`)");
    }
}

?>
