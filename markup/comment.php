<?php

namespace optimality;

class Comment extends Html {

    const DBSPAM = 'comment_dbspam';
    const DBPING = 'comment_dbping';
    const DBMETA = 'comment_dbmeta';
    const UNLINK = 'comment_unlink';
    const UNPAGE = 'comment_unpage';
    const SEMETA = 'comment_semeta';
    const SENAME = 'comment_sename';
    const SEDESC = 'comment_sedesc';
    const SMMETA = 'comment_smmeta';
    const SMNAME = 'comment_smname';
    const SMDESC = 'comment_smdesc';


    static function route($origin, $noteid = NULL) {
        return !is_null($noteid) ? get_comment_link($noteid) : preg_replace(
            '/[\?&]?replytocom=(\d+)(?:#respond)?/', '#comment-$1', $origin);
    }


    // ACTIONS

    static function countSpam() {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->comments}` WHERE comment_approved IN('spam','trash')");
    }


    static function cleanSpam() {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->comments}` WHERE comment_approved IN('spam','trash')");
    }


    static function countPing() {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->comments}` WHERE comment_type IN('pingback','trackback')");
    }


    static function cleanPing() {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->comments}` WHERE comment_type IN('pingback','trackback')");
    }


    static function countMeta() {
        return $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM `{$GLOBALS['wpdb']->commentmeta}` WHERE comment_id NOT IN (SELECT comment_id FROM `{$GLOBALS['wpdb']->comments}`)");
    }


    static function cleanMeta() {
        return $GLOBALS['wpdb']->query("DELETE FROM `{$GLOBALS['wpdb']->commentmeta}` WHERE comment_id NOT IN (SELECT comment_id FROM `{$GLOBALS['wpdb']->comments}`)");
    }
}

?>
