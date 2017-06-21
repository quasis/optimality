<?php

namespace optimality;

class Page extends Html {

    const SEMETA = 'page_semeta';
    const SENAME = 'page_sename';
    const SEDESC = 'page_sedesc';
    const SMMETA = 'page_smmeta';
    const SMNAME = 'page_smname';
    const SMDESC = 'page_smdesc';

    public $notes;


    function __construct($object) {

        parent::__construct($object);

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


    function __invoke($target, $option) {

        if (isset($option[static::SEMETA])) {

            add_filter('post_class', function($vector) {
                return array_diff($vector, ['hentry']);
            });
        }

        if (isset($option[Comment::UNLINK])) {

            if ($source = @$_GET['replytocom']) {
                return Comment::route(NULL, $source);
            }
        }

        if (isset($option[Comment::UNPAGE])) {

            if (get_query_var('cpage')) {
                return $this->route;
            }
        }

        return parent::__invoke($target, $option);
    }


    function getMeta($option) {

        return array_merge(parent::getMeta($option), [

            'og:type'                => 'article',
            'article:published_time' => $this->date,
            'article:modified_time'  => $this->edit,
            'article:author'         => $this->user->face,
            'article:publisher'      => $this->site->face,
        ]);
    }


    function getJson($option) {

        return array_merge(parent::getJson($option), [

            '@type'                  => 'WebPage',
            'headline'               => $this->name,
            'datePublished'          => $this->date,
            'dateModified'           => $this->edit,
            'commentCount'           => $this->notes,
            'author'                 => $this->user->goog,
            'publisher'              => $this->site->goog,
        ]);
    }


    static function fetch() {
        return static::encap(get_posts(['numberposts' => -1, 'post_type' => 'page', 'exclude' => get_option('page_on_front')]));
    }
}

?>
