<?php

namespace optimality;

class Term extends Html
{
    const DBLINK = 'term_dblink';
    const SEMETA = 'term_semeta';
    const SENAME = 'term_sename';
    const SEDESC = 'term_sedesc';
    const SMMETA = 'term_smmeta';
    const SMNAME = 'term_smname';
    const SMDESC = 'term_smdesc';

    public $items;


    function __construct($object)
    {
        parent::__construct($object);

        $this->type  = 'ItemList';
        $this->ruid  = $object->term_id;
        $this->slug  = $object->slug;
        $this->name  = $object->name;
        $this->desc  = $object->description;
        $this->route = get_term_link($object);
        $this->items = [ ];
    }


    function __invoke($target, $option)
    {
        foreach (@$GLOBALS['wp_query']->posts ?: [] as $offset => $object)
        {
            $this->items[] = array
            (
                '@type'    => 'ListItem',
                'position' => $offset + 1,
                'url'      => get_permalink($object),
            );
        }

        return parent::__invoke($target, $option);
    }


    function getJson($option)
    {
        return array_merge(parent::getJson($option),
        [
            'publisher'       => NULL,
            'author'          => NULL,
            'numberOfItems'   => count($this->items),
            'itemListElement' => $this->items,
        ]);
    }


    static function fetch()
    {
        return static::encap(get_terms(['taxonomy' => 'post_tag']));
    }


    static function countLink()
    {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->term_relationships}` WHERE term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM `{$GLOBALS['wpdb']->term_taxonomy}`)");
    }


    static function cleanLink()
    {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->term_relationships}` WHERE term_taxonomy_id NOT IN (SELECT term_taxonomy_id FROM `{$GLOBALS['wpdb']->term_taxonomy}`)");
    }
}

?>
