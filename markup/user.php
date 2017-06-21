<?php

namespace optimality;

class User extends Html {

    const UNLINK = 'user_unlink';
    const SEMETA = 'user_semeta';
    const SENAME = 'user_sename';
    const SEDESC = 'user_sedesc';
    const SMMETA = 'user_smmeta';
    const SMNAME = 'user_smname';
    const SMDESC = 'user_smdesc';

    public $fname;
    public $lname;
    public $goog;
    public $face;
    public $twit;


    function __construct($object) {

        parent::__construct($object);

        $this->ruid  = $object->ID;
        $this->slug  = $object->data->user_nicename;
        $this->name  = $object->data->display_name;
        $this->route = get_author_posts_url($object->ID);
        $this->user  = $this;

        if ($detail = get_userdata($object->ID)) {

            $this->desc  = $detail->description;
            $this->fname = $detail->first_name;
            $this->lname = $detail->last_name;
            $this->image = @$detail->image;
            $this->goog  = @$detail->googleplus;
            $this->face  = @$detail->facebook;
            $this->twit  = @$detail->twitter;
        }
    }


    function __invoke($target, $option) {

        if (isset($option[static::UNLINK])) {
            return static::route($target, NULL);
        }

        return parent::__invoke($target, $option);
    }


    function getMeta($option) {

        return array_merge(parent::getMeta($option), [

            'twitter:card'       => 'summary',
            'og:type'            => 'profile',
            'profile:username'   => $this->slug,
            'profile:first_name' => $this->fname,
            'profile:last_name'  => $this->lname,
            'profile:gender'     => NULL,
        ]);
    }


    function getJson($option) {

        return array_merge(parent::getJson($option), [

            '@type'              => 'Person',
            'givenName'          => $this->fname,
            'familyName'         => $this->lname,
        ]);
    }


    static function route($origin, $userid) {
        return home_url('/');
    }


    static function fetch() {
        return static::encap(get_users(['orderby' => 'registered', 'order' => 'DESC']));
    }
}

?>
