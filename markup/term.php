<?php

namespace optimality;

class Term extends Html {

    const DBLINK = 'term_dblink';
    const SEMETA = 'term_semeta';
    const SENAME = 'term_sename';
    const SEDESC = 'term_sedesc';
    const SMMETA = 'term_smmeta';
    const SMNAME = 'term_smname';
    const SMDESC = 'term_smdesc';

    public $posts;


    function __construct($object) {

        parent::__construct($object);

        $this->ruid  = $object->term_id;
        $this->slug  = $object->slug;
        $this->name  = $object->name;
        $this->desc  = $object->description;
        $this->route = get_term_link($object);
        $this->posts = @$GLOBALS['wp_query']->posts ?: [];
    }


    function getJson($option) {

        $offset = 0;

        return array_merge(parent::getJson($option), [

            '@type'              => 'ItemList',
            'numberOfItems'      => count($this->posts),
            'itemListElement'    => array_map(function($object) use(&$offset) {

                return [
                    '@type'      => 'ListItem',
                    'position'   => ++$offset,
                    'url'        => get_permalink($object)
                ];

            }, $this->posts),
        ]);
    }


    static function fetch() {
        return static::encap(get_terms(['taxonomy' => 'post_tag']));
    }


    static function countLink() {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->term_relationships}` WHERE term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM `{$GLOBALS['wpdb']->term_taxonomy}`)");
    }


    static function cleanLink() {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->term_relationships}` WHERE term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM `{$GLOBALS['wpdb']->term_taxonomy}`)");
    }
}

?>
