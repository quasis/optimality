<?php

/*
 * Plugin Name: Optimality
 * Plugin URI:  https://wordpress.org/plugins/optimality
 * Description: Optimizes website's content delivery, images, database, permalink structure, search engines and social media markup.
 * Version:     0.6.1
 * License:     GPLv2 or later
 * Author:      Quasis
 * Author URI:  https://github.com/quasis
 * Text Domain: optimality
 */

namespace optimality;

require_once('markup/html.php');
require_once('markup/site.php');
require_once('markup/page.php');
require_once('markup/post.php');
require_once('markup/term.php');
require_once('markup/section.php');
require_once('markup/user.php');
require_once('markup/comment.php');
require_once('markup/media.php');
require_once('markup/error.php');
require_once('markup/sitemap.php');

require_once('markup/image.php');
require_once('markup/style.php');
require_once('markup/script.php');
require_once('markup/font.php');


class Plugin {

    const WIDGET = 'widget';

    protected $preset;
    protected $option;
    protected $attrib;
    protected $action = [];


    function __construct() {

        if (!($option = get_option(__NAMESPACE__))) {

            add_option(__NAMESPACE__, $option = []);
        }

        $this->option = array_merge($this->preset = [

            Html::CUSTOM    => NULL,
            Plugin::WIDGET  => 'on',

            Html::UNMETA    => NULL,
            Html::UNEMOJ    => NULL,
            Html::PREDNS    => NULL,
            Html::MINIFY    => NULL,
            Html::CACHE     => NULL,
            Style::MINIFY   => NULL,
            Style::CDNLIB   => NULL,
            Script::MINIFY  => NULL,
            Script::CDNLIB  => NULL,
            Image::MINIFY   => NULL,
            Image::SRCSET   => NULL,
            Html::CDNURL    => __CDNURL__,

            Site::DBTEMP    => NULL,
            Post::DBAUTO    => NULL,
            Post::DBEDIT    => NULL,
            Post::DBMETA    => NULL,
            Term::DBLINK    => NULL,
            Comment::DBSPAM => NULL,
            Comment::DBPING => NULL,
            Comment::DBMETA => NULL,

            Section::UNBASE => NULL,
            Comment::UNLINK => NULL,
            Comment::UNPAGE => NULL,
            User::UNLINK    => NULL,
            Media::UNLINK   => NULL,
            Error::PARENT   => NULL,

            Site::SEMETA    => NULL,
            Site::SENAME    => ':name - :tagline',
            Site::SEDESC    => ':tagline',
            Page::SEMETA    => NULL,
            Page::SENAME    => ':name - site:name',
            Page::SEDESC    => ':excerpt',
            Post::SEMETA    => NULL,
            Post::SENAME    => ':name - :category',
            Post::SEDESC    => ':excerpt',
            Section::SEMETA => NULL,
            Section::SENAME => ':name - site:name',
            Section::SEDESC => ':description',
            Term::SEMETA    => NULL,
            Term::SENAME    => ':name - site:name',
            Term::SEDESC    => ':description',
            User::SEMETA    => NULL,
            User::SENAME    => ':name - site:name',
            User::SEDESC    => ':biography',
            Media::SEMETA   => NULL,
            Media::SENAME   => ':caption - site:name',
            Media::SEDESC   => ':description',

            Site::SMMETA    => NULL,
            Site::SMNAME    => ':name',
            Site::SMDESC    => ':tagline',
            Page::SMMETA    => NULL,
            Page::SMNAME    => ':name',
            Page::SMDESC    => ':excerpt',
            Post::SMMETA    => NULL,
            Post::SMNAME    => ':name',
            Post::SMDESC    => ':excerpt',
            Section::SMMETA => NULL,
            Section::SMNAME => ':name',
            Section::SMDESC => ':description',
            Term::SMMETA    => NULL,
            Term::SMNAME    => ':name',
            Term::SMDESC    => ':description',
            User::SMMETA    => NULL,
            User::SMNAME    => ':name',
            User::SMDESC    => ':biography',
            Media::SMMETA   => NULL,
            Media::SMNAME   => ':caption',
            Media::SMDESC   => ':description',

        ], maybe_unserialize($option));


        $this->attrib = array(

            'image'      => __('Image URL'),
            'googleplus' => __('Google+ Page'),
            'facebook'   => __('Facebook Page' ),
            'twitter'    => __('Twitter Handle'),
        );

        register_activation_hook(__FILE__, function() {
            if (!wp_next_scheduled( __NAMESPACE__ )) {
                wp_schedule_event(time(), 'daily', __NAMESPACE__);
            }
        });


        register_deactivation_hook(__FILE__, function() {
            wp_clear_scheduled_hook( __NAMESPACE__ ); $this->onReboot();
        });


        add_action(__NAMESPACE__, function() {

            isset($this->option[Site::DBTEMP]) && Site::cleanTemp();
            isset($this->option[Post::DBAUTO]) && Post::cleanAuto();
            isset($this->option[Post::DBEDIT]) && Post::cleanEdit();
            isset($this->option[Post::DBMETA]) && Post::cleanMeta();
            isset($this->option[Term::DBLINK]) && Term::cleanLink();
            isset($this->option[Comment::DBSPAM]) && Comment::cleanSpam();
            isset($this->option[Comment::DBPING]) && Comment::cleanPing();
            isset($this->option[Comment::DBMETA]) && Comment::cleanMeta();
        });

        add_action('switch_theme', [$this, 'onReboot']);

        if (isset($this->option[Image::MINIFY])) {
            add_filter('wp_image_editors', [Image::class, 'mount']);
        }

        call_user_func([ $this, '__construct' . (is_admin() ? 'Admin' : 'Front') ]);
    }


    function __constructFront() {

        if (isset($this->option[Html::UNMETA])) {

            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
            remove_action('wp_head', 'feed_links_extra', 3);
            remove_action('wp_head', 'rest_output_link_wp_head');
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
            remove_action('wp_head', 'wp_shortlink_wp_head');
        }

        if (isset($this->option[Html::UNEMOJ])) {

            remove_filter('comment_text_rss' , 'wp_staticize_emoji');
            remove_filter('the_content_feed' , 'wp_staticize_emoji');
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            remove_action('wp_print_styles'  , 'print_emoji_styles');
        }

        if (isset($this->option[Html::PREDNS])) {

            remove_action('wp_head', 'wp_resource_hints', 2);
        }

        if (isset($this->option[Section::UNBASE])) {

            add_filter('request'  , [Section::class, 'query'], 10, 1);
            add_filter('term_link', [Section::class, 'route'], 10, 3);
        }

        if (isset($this->option[Comment::UNLINK])) {

            add_filter('comment_reply_link', [Comment::class, 'route'], 10, 1);
        }

        if (isset($this->option[User::UNLINK])) {

            add_filter('author_link', [User::class, 'route'], 10, 2);
        }

        if (isset($this->option[Media::UNLINK])) {

            add_filter('attachment_link', [Media::class, 'route'], 10, 2);
        }

        if (isset($this->option[Image::SRCSET])) {

            remove_filter('the_content', 'wp_make_content_images_responsive');
        }

        if (isset($this->option[Style::CDNLIB])) {

            add_filter('style_loader_src', [Style::class, 'serve'], 10, 2);
        }

        if (isset($this->option[Script::MINIFY])) {

            remove_action('comment_form', 'wp_comment_form_unfiltered_html_nonce');
        }

        if (isset($this->option[Script::CDNLIB])) {

            add_filter('script_loader_src', [Script::class, 'serve'], 10, 2);
        }

        add_action('template_redirect', function() {

            switch (true) {

                case is_feed()      :
                case is_robots()    : return;
                case is_front_page(): $markup = Site::class;    break;
                case is_attachment(): $markup = Media::class;   break;
                case is_page()      : $markup = Page::class;    break;
                case is_single()    : $markup = Post::class;    break;
                case is_category()  : $markup = Section::class; break;
                case is_tag()       : $markup = Term::class;    break;
                case is_author()    : $markup = User::class;    break;
                case is_date()      :
                case is_search()    :
                case is_archive()   : $markup = Html::class;    break;
                case is_sitemap()   : $markup = Sitemap::class; break;
                case is_404()       : $markup = Error::class;   break;
                default             : return;
            }

            $markup = new $markup(get_queried_object());

            if ($target = $markup(__TARGET__, $this->option)) {
                wp_redirect($target, 301); exit();
            }

            $ishtml = preg_grep(Html::HEADER, headers_list());
            $method = strtoupper(@$_SERVER['REQUEST_METHOD']);
            
            if ($static = isset( $this->option[ Html::CACHE ] ) &&
                $ishtml && empty($_REQUEST) && $method === 'GET' &&
                !is_user_logged_in() && !defined('DOING_CRON')) {

                $markup->serve(@$_SERVER['HTTP_ACCEPT_ENCODING']);
            }

            $ishtml && ob_start(function($string) use($markup, $static) {

                return call_user_func(array($markup, $static ?
                    'cache' : 'build'), $string, $this->option);
            });
        });


        isset($this->option[Plugin::WIDGET]) && add_action('admin_bar_menu', function($widget) {

            $this->addWidget($widget,        NULL,   ucwords(__NAMESPACE__    ), $this->urlPlugin());
            $this->addWidget($widget, 'pagespeed', __('PageSpeed Insights'    ), 'https://developers.google.com/speed/pagespeed/insights/' , ['url'  => __TARGET__]);
            $this->addWidget($widget, 'mobility' , __('Mobile Friendly Test'  ), 'https://www.google.com/webmasters/tools/mobile-friendly/', ['url'  => __TARGET__]);
            $this->addWidget($widget, 'htmlvalid', __('W3C HTML Validator'    ), 'https://validator.w3.org/check'                          , ['uri'  => __TARGET__]);
            $this->addWidget($widget, 'cssvalid' , __('W3C CSS Validator'     ), 'https://jigsaw.w3.org/css-validator/validator'           , ['uri'  => __TARGET__]);
            $this->addWidget($widget, 'structure', __('Structured Data Test'  ), 'https://search.google.com/structured-data/testing-tool?hl=en#url=' . urlencode(__TARGET__));
            $this->addWidget($widget, 'cardvalid', __('Twitter Card Validator'), 'https://cards-dev.twitter.com/validator'                 , ['url'  => __TARGET__]);
            $this->addWidget($widget, 'opengraph', __('Open Graph Debugger'   ), 'https://developers.facebook.com/tools/debug/og/object'   , ['q'    => __TARGET__]);
            $this->addWidget($widget, 'pinvalid' , __('Rich Pin Validator'    ), 'https://developers.pinterest.com/tools/url-debugger/'    , ['link' => __TARGET__]);

        }, 100);
    }


    function __constructAdmin() {

        add_filter('plugin_action_links', function($action, $plugin) {

            return ((basename(__DIR__) . '/index.php') !== $plugin) ? $action : array_merge([
                sprintf('<a href="%s">%s</a>', $this->urlPlugin(), __('Settings'))], $action);

        }, 10, 2);


        add_action('admin_menu' , function() {

            add_options_page(ucwords(__NAMESPACE__), ucwords(__NAMESPACE__),
                'manage_options', __NAMESPACE__, array( $this, 'onScreen' ));
        });


        add_action('admin_init' , function() {

            load_plugin_textdomain(__NAMESPACE__, false, basename(__DIR__) . '/locale');
            register_setting(__NAMESPACE__, __NAMESPACE__, array( $this, 'onSubmit' ));

            $this->addModule($module = 'gen', __('General Settings'     ));
            $this->addOption(Html::CUSTOM   , __('Custom HTML'          ), $module, 'editor', __('This code will be injected into the head section of every HTML page.'));
            $this->addOption(Plugin::WIDGET , __('Admin Bar Menu'       ), $module, 'binary', __('Display a menu on the Admin Bar when browsing as admin.'));

            $this->addModule($module = 'cdo', __('Content Delivery'     ));
            $this->addOption(Html::UNMETA   , __('Clean Meta Tags'      ), $module, 'binary', __('Remove unnecessary meta tags from the head section of HTML.'));
            $this->addOption(Html::UNEMOJ   , __('Disable Emojis'       ), $module, 'binary', __('Remove styles and scripts of the new WordPress emoji feature.'));
            $this->addOption(Html::PREDNS   , __('Prefetch DNS'         ), $module, 'binary', __('Reduce DNS lookup time by pre-resolving all external domains.'));
            $this->addOption(Html::MINIFY   , __('Optimize HTML'        ), $module, 'binary', __('Remove comments, unnecessary whitespace and empty nodes.'));
            $this->addOption(Html::CACHE    , __('Cache HTML'           ), $module, 'binary', __('Cache dynamic HTML content and serve it as static HTML files.'));
            $this->addAction(Html::CACHE    , __('Clean HTML Cache'     ), 'trash', [Html::class, 'cleanCache'], [Html::class, 'countCache']);
            $this->addOption(Style::MINIFY  , __('Optimize Styles'      ), $module, 'binary', __('Combine files, flatten imports, remove comments and cache.'));
            $this->addAction(Style::MINIFY  , __('Clean Style Cache'    ), 'trash', [Style::class, 'cleanCache'], [Style::class, 'countCache']);
            $this->addOption(Style::CDNLIB  , __('Offload Styles'       ), $module, 'binary', __('Serve popular CSS libraries from content delivery networks.'));
            $this->addOption(Script::MINIFY , __('Optimize Scripts'     ), $module, 'binary', __('Combine files, defer loading, remove comments and cache.'));
            $this->addAction(Script::MINIFY , __('Clean Script Cache'   ), 'trash', [Script::class, 'cleanCache'], [Script::class, 'countCache']);
            $this->addOption(Script::CDNLIB , __('Offload Scripts'      ), $module, 'binary', __('Serve popular JS libraries from content delivery networks.'));
            $this->addOption(Image::MINIFY  , __('Compress Thumbs'      ), $module, 'binary', __('Strip metadata and compress thumbnail images during upload.'));
            $this->addAction(Image::MINIFY  , __('Rebuild Thumbnails (Long Processing Time)'), 'images-alt', [Image::class, 'buildCache'], [Image::class, 'countCache']);
            $this->addOption(Image::SRCSET  , __('Optimize Images'      ), $module, 'binary', __('Canonicalize URLs, add \'srcset\' attributes and serve from CDN.'));
            $this->addOption(Html::CDNURL   , __('Content Base'         ), $module, 'string', __('URL that resolves to the uploads directory, like \'//cdn.domain.com/\'.'));

            $this->addModule($module = 'dbo', __('Database Storage'     ));
            $this->addOption(Site::DBTEMP   , __('Option Transients'    ), $module, 'binary', __('Automatically remove expired transients from the database.'));
            $this->addAction(Site::DBTEMP   , __('Clean Manually'       ), 'trash', [Site::class, 'cleanTemp'], [Site::class, 'countTemp']);
            $this->addOption(Post::DBAUTO   , __('Post Autodrafts'      ), $module, 'binary', __('Automatically remove automatic post drafts from the database.'));
            $this->addAction(Post::DBAUTO   , __('Clean Manually'       ), 'trash', [Post::class, 'cleanAuto'], [Post::class, 'countAuto']);
            $this->addOption(Post::DBEDIT   , __('Post Revisions'       ), $module, 'binary', __('Automatically remove post revision history from the database.'));
            $this->addAction(Post::DBEDIT   , __('Clean Manually'       ), 'trash', [Post::class, 'cleanEdit'], [Post::class, 'countEdit']);
            $this->addOption(Post::DBMETA   , __('Post Meta'            ), $module, 'binary', __('Automatically remove orphaned post meta from the database.'));
            $this->addAction(Post::DBMETA   , __('Clean Manually'       ), 'trash', [Post::class, 'cleanMeta'], [Post::class, 'countMeta']);
            $this->addOption(Term::DBLINK   , __('Term Relationships'   ), $module, 'binary', __('Automatically remove orphaned relationships from the database.'));
            $this->addAction(Term::DBLINK   , __('Clean Manually'       ), 'trash', [Term::class, 'cleanLink'], [Term::class, 'countLink']);
            $this->addOption(Comment::DBSPAM, __('Comment Trash'        ), $module, 'binary', __('Automatically remove trash/spam comments from the database.'));
            $this->addAction(Comment::DBSPAM, __('Clean Manually'       ), 'trash', [Comment::class, 'cleanSpam'], [Comment::class, 'countSpam']);
            $this->addOption(Comment::DBPING, __('Comment Linkbacks'    ), $module, 'binary', __('Automatically remove pingbacks/trackbacks from the database.'));
            $this->addAction(Comment::DBPING, __('Clean Manually'       ), 'trash', [Comment::class, 'cleanPing'], [Comment::class, 'countPing']);
            $this->addOption(Comment::DBMETA, __('Comment Meta'         ), $module, 'binary', __('Automatically remove orphaned meta data from the database.'));
            $this->addAction(Comment::DBMETA, __('Clean Manually'       ), 'trash', [Comment::class, 'cleanMeta'], [Comment::class, 'countMeta']);

            $this->addModule($module = 'wso', __('Website Structure'    ));
            $this->addOption(Section::UNBASE, __('Remove Category Base' ), $module, 'binary', __('Remove the mandatory base from the permalinks of categories.'));
            $this->addOption(User::UNLINK   , __('Disable User Archives'), $module, 'binary', __('Redirect author archive pages to the home page of the website.'));
            $this->addOption(Media::UNLINK  , __('Disable Attachments'  ), $module, 'binary', __('Redirect image attachment pages to the URL of the parent page.'));
            $this->addOption(Comment::UNLINK, __('Disable Reply Queries'), $module, 'binary', __('Redirect ?replytocom=id to #comment-id in comment replies.'));
            $this->addOption(Comment::UNPAGE, __('Depaginate Comments'  ), $module, 'binary', __('Redirect paginated comments to the URL of the parent page.'));
            $this->addOption(Error::PARENT  , __('Redirect 404 Errors'  ), $module, 'binary', __('Redirect 404 error pages one level up in the website hierarchy.'));

            $this->addModule($module = 'seo', __('Search Engines'       ));
            $this->addOption(Site::SEMETA   , __('Homepage'             ), $module, 'binary', __('Insert title, meta tags, schema, and include in the sitemap.'));
            $this->addOption(Site::SENAME   , __('Title Template'       ), $module, 'string', null, Site::SEMETA);
            $this->addOption(Site::SEDESC   , __('Desc. Template'       ), $module, 'string', null, Site::SEMETA);
            $this->addOption(Page::SEMETA   , __('Pages'                ), $module, 'binary', __('Insert titles, meta tags, schema, and include in the sitemap.'));
            $this->addOption(Page::SENAME   , __('Title Template'       ), $module, 'string', null, Page::SEMETA);
            $this->addOption(Page::SEDESC   , __('Desc. Template'       ), $module, 'string', null, Page::SEMETA);
            $this->addOption(Post::SEMETA   , __('Posts'                ), $module, 'binary', __('Insert titles, meta tags, schema, and include in the sitemap.'));
            $this->addOption(Post::SENAME   , __('Title Template'       ), $module, 'string', null, Post::SEMETA);
            $this->addOption(Post::SEDESC   , __('Desc. Template'       ), $module, 'string', null, Post::SEMETA);
            $this->addOption(Section::SEMETA, __('Categories'           ), $module, 'binary', __('Insert titles, meta tags, schema, and include in the sitemap.'));
            $this->addOption(Section::SENAME, __('Title Template'       ), $module, 'string', null, Section::SEMETA);
            $this->addOption(Section::SEDESC, __('Desc. Template'       ), $module, 'string', null, Section::SEMETA);
            $this->addOption(Term::SEMETA   , __('Tags'                 ), $module, 'binary', __('Insert titles, meta tags, schema, and include in the sitemap.'));
            $this->addOption(Term::SENAME   , __('Title Template'       ), $module, 'string', null, Term::SEMETA);
            $this->addOption(Term::SEDESC   , __('Desc. Template'       ), $module, 'string', null, Term::SEMETA);
            $this->addOption(User::SEMETA   , __('User Archives'        ), $module, 'binary', __('Insert titles, meta tags, schema, and include in the sitemap.'));
            $this->addOption(User::SENAME   , __('Title Template'       ), $module, 'string', null, User::SEMETA);
            $this->addOption(User::SEDESC   , __('Desc. Template'       ), $module, 'string', null, User::SEMETA);
            $this->addOption(Media::SEMETA  , __('Media Attachments'    ), $module, 'binary', __('Insert titles, meta tags, schema, and include in the sitemap.'));
            $this->addOption(Media::SENAME  , __('Title Template'       ), $module, 'string', null, Media::SEMETA);
            $this->addOption(Media::SEDESC  , __('Desc. Template'       ), $module, 'string', null, Media::SEMETA);

            $this->addModule($module = 'smo', __('Social Media'         ));
            $this->addOption(Site::SMMETA   , __('Homepage'             ), $module, 'binary', __('Insert meta tags to integrate into Open Graph and Twitter.'));
            $this->addOption(Site::SMNAME   , __('Title Template'       ), $module, 'string', null, Site::SMMETA);
            $this->addOption(Site::SMDESC   , __('Desc. Template'       ), $module, 'string', null, Site::SMMETA);
            $this->addOption(Page::SMMETA   , __('Pages'                ), $module, 'binary', __('Insert meta tags to integrate into Open Graph and Twitter.'));
            $this->addOption(Page::SMNAME   , __('Title Template'       ), $module, 'string', null, Page::SMMETA);
            $this->addOption(Page::SMDESC   , __('Desc. Template'       ), $module, 'string', null, Page::SMMETA);
            $this->addOption(Post::SMMETA   , __('Posts'                ), $module, 'binary', __('Insert meta tags to integrate into Open Graph and Twitter.'));
            $this->addOption(Post::SMNAME   , __('Title Template'       ), $module, 'string', null, Post::SMMETA);
            $this->addOption(Post::SMDESC   , __('Desc. Template'       ), $module, 'string', null, Post::SMMETA);
            $this->addOption(Section::SMMETA, __('Categories'           ), $module, 'binary', __('Insert meta tags to integrate into Open Graph and Twitter.'));
            $this->addOption(Section::SMNAME, __('Title Template'       ), $module, 'string', null, Section::SMMETA);
            $this->addOption(Section::SMDESC, __('Desc. Template'       ), $module, 'string', null, Section::SMMETA);
            $this->addOption(Term::SMMETA   , __('Tags'                 ), $module, 'binary', __('Insert meta tags to integrate into Open Graph and Twitter.'));
            $this->addOption(Term::SMNAME   , __('Title Template'       ), $module, 'string', null, Term::SMMETA);
            $this->addOption(Term::SMDESC   , __('Desc. Template'       ), $module, 'string', null, Term::SMMETA);
            $this->addOption(User::SMMETA   , __('User Archives'        ), $module, 'binary', __('Insert meta tags to integrate into Open Graph and Twitter.'));
            $this->addOption(User::SMNAME   , __('Title Template'       ), $module, 'string', null, User::SMMETA);
            $this->addOption(User::SMDESC   , __('Desc. Template'       ), $module, 'string', null, User::SMMETA);
            $this->addOption(Media::SMMETA  , __('Media Attachments'    ), $module, 'binary', __('Insert meta tags to integrate into Open Graph and Twitter.'));
            $this->addOption(Media::SMNAME  , __('Title Template'       ), $module, 'string', null, Media::SMMETA);
            $this->addOption(Media::SMDESC  , __('Desc. Template'       ), $module, 'string', null, Media::SMMETA);

            foreach ($this->attrib as $handle => $header) {

                add_settings_field($handle, $header, [$this, 'onAttrib'],
                    'general', 'default', array('label_for' => $handle));

                register_setting('general', $handle);
            }
        });


        add_filter('user_contactmethods', function($method) {

            return array_merge($method, $this->attrib);

        }, 10, 1);


        add_action('transition_post_status', function($status, $former) {

            $status === 'publish' && $status !== $former && Sitemap::ping();

        }, 10, 2);


        add_filter('media_row_actions', function($action, $object) {

            if (strpos($object->post_mime_type, 'image/') === 0) {

                array_push($action, sprintf('<a href="%s">%s</a>', $this->urlAction(
                    Image::MINIFY, ['ids' => $object->ID]), __('Rebuild Thumbnails')));
            }

            return $action;

        }, 10, 2 );


        add_filter('bulk_actions-upload', function($action) {

            return [$this->getHandle(Image::MINIFY) => __('Rebuild Thumbnails')] + $action;
        });


        add_filter('handle_bulk_actions-upload', function($target, $action, $postid) {

            return ($action !== $this->getHandle(Image::MINIFY)) ? $target :
                $this->urlAction(Image::MINIFY, ['ids' => implode(',', $postid)]);

        }, 10, 3);

        add_post_type_support('page', 'excerpt');
    }


    function urlPlugin() {
        return admin_url('options-general.php?page=' . __NAMESPACE__);
    }


    function getHandle($handle) {
        return strpos($handle, __NAMESPACE__) === 0 ? $handle :
            sprintf('%s[%s]', __NAMESPACE__, $handle);
    }


    function addModule($handle, $header) {
        add_settings_section($handle, $header, null, __NAMESPACE__);
    }


    function getModules() {
        return $GLOBALS['wp_settings_sections'][__NAMESPACE__];
    }


    function addOption($handle, $header, $module, $format, $detail, $parent = NULL) {
        add_settings_field($handle, $header, null, __NAMESPACE__,
            $module, ['object' => (object) get_defined_vars( ) ]);
    }


    function getOptions($module) {
        return @$GLOBALS['wp_settings_fields'][__NAMESPACE__][$module] ?: [];
    }


    function addAction($handle, $header, $symbol, $method, $status = NULL) {

        $handle = $this->getHandle($handle);
        $this->action[ $handle ] = (object) get_defined_vars( );
        add_action("admin_post_{$handle}", [$this, 'onAction']);
    }


    function urlAction($handle, $fields = []) {
        return wp_nonce_url(admin_url('admin-post.php?' . http_build_query(
            array_merge(['action' => $this->getHandle($handle)], $fields))));
    }


    function addWidget($object, $handle, $header, $target, $fields = []) {

        $target = $target . ($fields ? ('?' . http_build_query($fields)) : '');

        $object->add_node(['id' => __NAMESPACE__ . $handle, 'title' => $header, 'href' => $target,
            'parent' =>  $handle ? __NAMESPACE__ : NULL, 'meta' => array('target' => 'blank') ]);
    }


    function onAttrib($detail) {

        $format = '<input type="text" id="%s" name="%s" value="%s" class="regular-text"/>';
        echo(sprintf($format, $handle = $detail['label_for'], $handle, get_option($handle)));
    }


    function onOption($object) {

        switch ($object->format) {

            case 'binary':
                $layout = '<label><input type="checkbox" data-parent="%s" id="%s" name="%s"%s/>&nbsp;%s</label>';
                $option = checked($this->option[$object->handle], 'on', false);
                break;

            case 'string':
                $layout = '<input type="text" data-parent="%s" id="%s" name="%s" value="%s" class="regular-text"/><p class="description">%s</p>';
                $option = esc_attr($this->option[$object->handle]);
                break;

            case 'editor':
                $layout = '<textarea data-parent="%s" id="%s" name="%s" class="large-text code" rows="6">%s</textarea><p class="description">%s</p>';
                $option = esc_attr($this->option[$object->handle]);
                break;

            case 'select':
                $layout = '<select data-parent="%s" id="%s" name="%s" class="regular-text">%s</select>';
                $option = '';

                foreach ($object->detail as $value => $label)
                {
                    $option .= sprintf('<option value="%s"%s>%s</option>', $value,
                        selected($value, $this->option[$object->handle], false), $label);
                }

                break;
        }

        $handle = $this->getHandle($object->handle);
        $parent = $this->getHandle($object->parent);

        if (($action = @$this->action[$handle]) && ($status = call_user_func($action->status))) {

            $action = sprintf('<button type="button" class="button button-small" data-action="%s" title="%s">' .
                '<span class="dashicons dashicons-%s"></span>&nbsp;<output>%d</output></button>',
                $this->urlAction($handle), $action->header, $action->symbol, $status);
        }
        else {

            $action = NULL;
        }

        $header = sprintf($object->parent ? 'td>%s</td' : 'th>%s</th', $object->header);
        $widget = sprintf($layout, $parent, $handle, $handle, $option, $object->detail);
        echo(sprintf("<tr><%s><td>%s</td><td>%s</td></tr>", $header, $widget, $action));
    }


    function onScreen() {

        current_user_can('manage_options') || die();

        ?>
        <div class="wrap">
            <h1><?= get_admin_page_title() ?></h1>

            <form action='options.php' method='post'>
                <?php settings_fields(__NAMESPACE__) ?>

                <h2 class="nav-tab-wrapper">
                    <?php foreach ($this->getModules() as $handle => $module): ?>
                        <a href="#<?= $handle ?>" class="nav-tab"><?= $module['title'] ?></a>
                    <?php endforeach; ?>
                </h2>

                <?php foreach ($this->getModules() as $handle => $module): ?>
                    <div id="<?= $handle ?>" class="nav-tab-content">
                        <table class="form-table">
                            <?php foreach ($this->getOptions($handle) as $option): ?>
                                <?php $this->onOption( $option['args']['object'] ) ?>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endforeach; ?>

                <?php submit_button() ?>
            </form>

            <style type="text/css" scoped>
                .nav-tab-content { display:none }
                .nav-tab-content:target { display:table }

                .spin { animation: spin 2s infinite linear }

                @keyframes spin
                {
                      0% { transform: rotate(  0deg) }
                    100% { transform: rotate(359deg) }
                }
            </style>

            <script src="<?= plugins_url('index.js?v=' . time(), __FILE__) ?>">
            </script>
        </div>
        <?php
    }


    function onAction() {

        current_user_can('manage_options') || die();

        $action = $this->action[ $_REQUEST[ 'action' ] ];
        $result = call_user_func($action->method, $_REQUEST);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_redirect($_SERVER['HTTP_REFERER']);
        }

        echo($result); exit();
    }


    function onSubmit($option) {

        foreach ($this->preset as $handle => $string) {

            if (@$option[$handle] === $string) {
                unset($option[$handle]);
            }
            else if (!isset($option[$handle])) {
                $option[$handle] = NULL;
            }
        }

        return $option;
    }


    function onReboot() {

        Style::cleanCache();
        Script::cleanCache();
        Html::cleanCache();
    }
}


function is_sitemap() {
    return wp_parse_url(@$_SERVER['REQUEST_URI'] ?: '/', PHP_URL_PATH) === Sitemap::PATH;
}


defined('ABSPATH') &&
define(__NAMESPACE__ . '\__SCHEME__', wp_parse_url(get_home_url(), PHP_URL_SCHEME)) &&
define(__NAMESPACE__ . '\__DOMAIN__', wp_parse_url(get_home_url(), PHP_URL_HOST  )) &&
define(__NAMESPACE__ . '\__TARGET__', sprintf('%s://%s%s', __SCHEME__, __DOMAIN__, $_SERVER['REQUEST_URI'])) &&
define(__NAMESPACE__ . '\__CDNDIR__', wp_get_upload_dir( )[  'basedir'  ] . '/') &&
define(__NAMESPACE__ . '\__CDNURL__', wp_get_upload_dir( )[  'baseurl'  ] . '/') &&
new Plugin();

?>
