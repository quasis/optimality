<?php

namespace optimality;

class Media extends Html
{
    const UNLINK = 'media_unlink';
    const SEMETA = 'media_semeta';
    const SENAME = 'media_sename';
    const SEDESC = 'media_sedesc';
    const SMMETA = 'media_smmeta';
    const SMNAME = 'media_smname';
    const SMDESC = 'media_smdesc';


    function __construct($object)
    {
        parent::__construct($object);

        $this->type  = 'ImageObject';
        $this->ruid  = $object->ID;
        $this->slug  = $object->post_name;
        $this->name  = $object->post_title;
        $this->desc  = $object->post_content;
        $this->lead  = $object->post_excerpt;
        $this->image = wp_get_attachment_url($object->ID);
        $this->route = get_attachment_link($object);
        $this->date  = date(DATE_W3C, strtotime($object->post_date));
        $this->edit  = date(DATE_W3C, strtotime($object->post_modified));
        $this->user  = $object->post_author;
    }


    static function route($origin, $fileid)
    {
        return get_permalink(wp_get_post_parent_id($fileid));
    }


    static function fetch()
    {
        return static::encap(get_posts(['numberposts' => -1, 'post_type' => 'attachment']));
    }
}

?>
