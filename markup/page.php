<?php

namespace optimality;

class Page extends Html
{
    const SEMETA = 'page_semeta';
    const SENAME = 'page_sename';
    const SEDESC = 'page_sedesc';
    const SMMETA = 'page_smmeta';
    const SMNAME = 'page_smname';
    const SMDESC = 'page_smdesc';


    function __construct($object)
    {
        parent::__construct($object);

        $this->type  = 'WebPage';
        $this->ruid  = $object->ID;
        $this->slug  = $object->post_name;
        $this->name  = $object->post_title;
        $this->lead  = $object->post_excerpt;
        $this->image = get_the_post_thumbnail_url($object, 'full');
        $this->route = get_page_link($object);
        $this->date  = date(DATE_W3C, strtotime($object->post_date));
        $this->edit  = date(DATE_W3C, strtotime($object->post_modified));
        $this->user  = $object->post_author;
    }


    function getMeta($option)
    {
        return array_merge(parent::getMeta($option),
        [
            'og:type'                => 'article',
            'article:published_time' => $this->date,
            'article:modified_time'  => $this->edit,
            'article:author'         => $this->user->face,
            'article:publisher'      => $this->site->face,
        ]);
    }


    static function fetch()
    {
        return static::encap(get_posts(['numberposts' => -1, 'post_type' => 'page', 'exclude' => get_option('page_on_front')]));
    }
}

?>
