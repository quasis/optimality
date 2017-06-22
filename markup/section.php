<?php

namespace optimality;

class Section extends Html
{
    const UNBASE = 'section_unbase';
    const SEMETA = 'section_semeta';
    const SENAME = 'section_sename';
    const SEDESC = 'section_sedesc';
    const SMMETA = 'section_smmeta';
    const SMNAME = 'section_smname';
    const SMDESC = 'section_smdesc';


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


    function apply($target, $option)
    {
        if ($option[static::UNBASE])
        {
            if (strpos($target, $this->route) !== 0)
            {
                return static::route($target, NULL);
            }
        }

        return parent::apply($target, $option);
    }


    static function route($origin, $termid, $prefix = 'category')
    {
        return ($prefix !== 'category') ? $origin : str_replace('/' .
            (get_option('category_base') ?: $prefix) . '/', '/', $origin);
    }


    static function query($fields)
    {
        if ($handle = @$fields['attachment'] ?: @$fields['name'])
        {
            $object = get_term_by('slug', $handle, 'category');

            if (is_object($object) && !is_wp_error($object))
            {
                unset($fields['name'], $fields['attachment']);
                return ['category_name' => $handle] + $fields;
            }
        }

        return $fields;
    }


    static function fetch()
    {
        return static::encap(get_terms(['taxonomy' => 'category']));
    }
}

?>
