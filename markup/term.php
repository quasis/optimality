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


    function __construct($object)
    {
        parent::__construct($object);

        $this->type  = 'CollectionPage';
        $this->ruid  = $object->term_id;
        $this->slug  = $object->slug;
        $this->name  = $object->name;
        $this->desc  = $object->description;
        $this->route = get_term_link($object);
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
