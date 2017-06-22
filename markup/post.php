<?php

namespace optimality;

class Post extends Html
{
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
    public $terms = [];
    public $notes;


    function __construct($object)
    {
        parent::__construct($object);

        $this->type  = 'Article';
        $this->ruid  = $object->ID;
        $this->slug  = $object->post_name;
        $this->name  = $object->post_title;
        $this->lead  = $object->post_excerpt;
        $this->image = get_the_post_thumbnail_url($object, 'full');
        $this->route = get_page_link($object);
        $this->date  = date(DATE_W3C, strtotime($object->post_date));
        $this->edit  = date(DATE_W3C, strtotime($object->post_modified));
        $this->user  = $object->post_author;
        $this->notes = intval($object->comment_count);
    }


    function getJson($option)
    {
        return array_merge(parent::getJson($option),
        [
            'headline'               => $this->name,
            'articleSection'         => $this->section,
            'keywords'               => implode(',' , $this->terms),
            'commentCount'           => $this->notes,
        ]);
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
            'article:section'        => $this->section,
            'article:tag'            => $this->terms,
        ]);
    }


    function apply($target, $option)
    {
        if (@$option[Comment::UNLINK])
        {
            if ($source = @$_GET['replytocom'])
            {
                return Comment::route(NULL, $source);
            }
        }

        if (is_array($result = get_the_terms($this->ruid, 'category')))
        {
            $this->section = @$result[0]->name;
        }

        if (is_array($result = get_the_terms($this->ruid, 'post_tag')))
        {
            foreach ($result as $object) $this->terms[] = $object->name; 
        }

        return parent::apply($target, $option);
    }


    static function fetch()
    {
        return static::encap(get_posts(['numberposts' => -1, 'post_type' => 'post']));
    }


    static function countAuto()
    {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->posts}` WHERE post_status='auto-draft'");
    }


    static function cleanAuto()
    {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->posts}` WHERE post_status='auto-draft'");
    }


    static function countEdit()
    {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->posts}` WHERE post_type='revision'");
    }


    static function cleanEdit()
    {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->posts}` WHERE post_type='revision'");
    }


    static function countMeta()
    {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->postmeta}` WHERE post_id NOT IN (SELECT ID FROM `{$GLOBALS['wpdb']->posts}`)");
    }


    static function cleanMeta()
    {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->postmeta}` WHERE post_id NOT IN (SELECT ID FROM `{$GLOBALS['wpdb']->posts}`)");
    }
}

?>
