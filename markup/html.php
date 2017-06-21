<?php

namespace optimality;

class Html extends \DOMDocument {

    const HEADER = '/^Content-Type\:\s*text\/html/';
    const OGMETA = '/^(og|article|profile):(.+)$/';
    const CUSTOM = 'html_custom';
    const UNMETA = 'html_unmeta';
    const UNEMOJ = 'html_unemoj';
    const PREDNS = 'html_predns';
    const MINIFY = 'html_minify';
    const CACHE  = 'html_static';
    const CDNURL = 'html_cdnurl';
    const SEMETA = 'html_semeta';
    const SENAME = 'html_sename';
    const SEDESC = 'html_sedesc';
    const SMMETA = 'html_smmeta';
    const SMNAME = 'html_smname';
    const SMDESC = 'html_smdesc';

    public $proto;
    public $root;
    public $head;
    public $body;
    public $meta = [];
    public $ruid;
    public $slug;
    public $name;
    public $desc;
    public $lead;
    public $image;
    public $route;
    public $date;
    public $edit;
    public $user;
    public $site;
    public $text;
    public $path;


    function __construct($object) {
        $this->proto = $object;
    }


    function __invoke($target, $option) {

        $this->site = $this->site ?: new Site();

        if ($this->user && is_numeric($this->user)) {
            $this->user = new User(get_userdata($this->user));
        }

        $this->text = array(

            ':name'        => $this->name,
            ':tagline'     => $this->lead,
            ':excerpt'     => $this->lead,
            ':caption'     => $this->lead,
            ':category'    => @$this->section,
            ':biography'   => $this->desc,
            ':description' => $this->desc,
            'site:name'    => @$this->site->name,
            'site:tagline' => @$this->site->lead,
        );

        $this->path = array(

            __CDNURL__ => $option[static::CDNURL],
        );
    }


    function setMeta($handle, $string) {

        if (is_array($string)) {

            array_map(function($member) use($handle) {
                $this->addMeta($handle, $member);

            }, $string);
        }
        else if ($string) {

            $attrib = preg_match(static::OGMETA,
                $handle) ? 'property' : 'name';

            if (!($object = @$this->meta[$handle])) {

                $object = $this->createElement('meta');
                $object->setAttribute($attrib, $handle);
                $this->head->appendChild($object);
            }

            $object->setAttribute('content', $string);
        }
    }


    function addMeta($handle, $string) {

        if ($string && $object = $this->createElement('meta')) {

            $attrib = preg_match(static::OGMETA,
                $handle) ? 'property' : 'name';

            $object->setAttribute($attrib  , $handle);
            $object->setAttribute('content', $string);
            $this->head->appendChild($object);
        }
    }


    function getMeta($option) {

        return array(

            'twitter:card'        => 'summary_large_image',
            'twitter:title'       => ucfirst(strtr(@$option[static::SMNAME], $this->text)),
            'twitter:description' => ucfirst(strtr(@$option[static::SMDESC], $this->text)),
            'twitter:image'       => strtr($this->image ?: $this->site->image, $this->path),
            'twitter:creator'     => $this->user ? $this->user->twit : NULL,
            'twitter:site'        => $this->site ? $this->site->twit : NULL,

            'og:type'             => 'website',
            'og:title'            => ucfirst(strtr(@$option[static::SMNAME], $this->text)),
            'og:description'      => ucfirst(strtr(@$option[static::SMDESC], $this->text)),
            'og:image'            => strtr($this->image ?: $this->site->image, $this->path),
            'og:url'              => $this->route,
            'og:locale'           => $this->site->lang,
            'og:site_name'        => $this->site->name,
            'og:updated_time'     => $this->edit,
        );
    }


    function addJson($source, $format) {

        if ($source && $object = $this->createElement('script')) {

            $object->setAttribute('type' , $format);
            $object->nodeValue = json_encode($source);
            $this->head->appendChild($object);
        }
    }


    function getJson($option) {

        return array(

            '@context'            => 'http://schema.org',
            '@type'               => 'Thing',
            'name'                => ucfirst(strtr(@$option[static::SENAME], $this->text)),
            'description'         => ucfirst(strtr(@$option[static::SEDESC], $this->text)),
            'image'               => strtr($this->image ?: $this->site->image, $this->path),
            'url'                 => $this->route,

            //'author'              => $this->user ? $this->user->goog : NULL,
            //'publisher'           => $this->site ? $this->site->goog : NULL,
            //'datePublished'       => $this->date,
            //'dateModified'        => $this->edit,
        );
    }


    function addLink($source, $relate, $format = NULL) {

        if ($source && $object = $this->createElement('link')) {

            $object->setAttribute('href', $source);
            $relate && $object->setAttribute('rel' , $relate);
            $format && $object->setAttribute('type', $format);
            $this->head->appendChild($object);
        }
    }


    function addScript($source, $defers, $format = NULL) {

        if ($source && $object = $this->createElement('script')) {

            $format && $object->setAttribute('type' , $format);
            $object->setAttribute('src', $source);
            $defers && $object->setAttribute('defer', NULL);
            $this->body->appendChild($object);
        }
    }


    function addHtml($source, $parent) {

        $parser = new \DOMDocument();
        @$parser->loadHTML($source);
        
        foreach (@$parser->documentElement->childNodes[0]->childNodes ?: [] as $object) {
            $parent->appendChild($parent->ownerDocument->importNode($object, true));
        }
    }


    function debug($object) {

        if ($script = $this->createElement('script')) {

            $this->body->appendChild($script);
            $script->nodeValue = 'console.log(' . json_encode($object) . ');';
        }
    }


    function build($string, $option) {

        $this->preserveWhiteSpace = !isset($option[static::MINIFY]);

        if (!@$this->loadHTML($string, LIBXML_COMPACT|LIBXML_NOBLANKS)) {
            return $string;
        }

        $this->root = $this->documentElement;
        $this->head = @$this->root->childNodes[0];
        $this->body = @$this->root->childNodes[1];

        if (empty($this->head) || empty($this->body)) {
            return $string;
        }

        $schema = new \DOMXPath($this); $linked = [ ];

        foreach ($schema->query('/html/head/meta') as $object) {
            $this->meta[$object->getAttribute('property') ?:
                $object->getAttribute('name')] = $object;
        }

        if ($source = @$option[static::CUSTOM]) {
            $this->addHtml(html_entity_decode($source), $this->head);
        }


        // SEO

        if (isset($option[static::SEMETA])) {

            if (!($object = @$schema->query('/html/head/title[1]')[0])) {
                $object = $this->createElement('title');
                $this->head->appendChild($object);
            }

            $object->nodeValue = ucfirst(strtr(@$option[static::SENAME], $this->text));

            $this->setMeta('description', ucfirst(strtr(@$option[static::SEDESC], $this->text)));
            $this->addJson(array_filter($this->getJson($option)), 'application/ld+json');
        }

        // SMO

        if (isset($option[static::SMMETA])) {

            $this->root->setAttribute('prefix', 'og: http://ogp.me/ns#');

            foreach ($this->getMeta($option) as $handle => $string) {
                $this->setMeta($handle, $string);
            }
        }

        // DNS

        if (isset($option[static::PREDNS])) {

            $filter = '//link[@rel="stylesheet"]|//script[@src]|/html/body//img[@src]';
            $domain = [ wp_parse_url($option[static::CDNURL], PHP_URL_HOST) => 1 ];

            foreach ($schema->query($filter) as $object) {
                $domain[ wp_parse_url($object->getAttribute('src') ?:
                    $object->getAttribute('href'), PHP_URL_HOST)] = 1;
            }

            unset($domain[NULL], $domain[__DOMAIN__]);

            foreach ($domain as $domain => $binary) {
                $this->addLink("//{$domain}", 'dns-prefetch');
            }
        }

        // CSS

        if (isset($option[Style::MINIFY])) {

            $bundle = new Style();
            $inline = new Style();
            $google = new Font();
            $filter = '//link[@rel="stylesheet"]|//style';

            foreach ($schema->query($filter) as $object) {

                $object->parentNode->removeChild($object);
                $render = $object->getAttribute('media') ?: 'all';

                if ($source = $object->getAttribute('href')) {

                    $domain = wp_parse_url($source, PHP_URL_HOST) ?: __DOMAIN__;

                    if (($domain === __DOMAIN__) && $bundle->valid($source)) {

                        $bundle->import($source, $render);
                    }
                    else if ($domain === 'fonts.googleapis.com') {

                        $google->import($source);
                    }
                    else {

                        $this->head->appendChild($object);
                        $linked[] = "<{$source}>; rel=preload; as=style";
                    }
                }
                else if ($source = trim($object->textContent)) {

                    if ($bundle->valid($source)) {
                        $bundle->append($source, $render);
                    }
                    else {
                        $inline->append($source, $render);
                    }
                }
            }

            if (!empty($inline->code)) {

                $object = $this->createElement('style');
                $object->nodeValue = implode("\n", $inline->code);
                $this->head->appendChild($object);
            }

            if (!empty($bundle->code)) {

                $source = $bundle->cache($option[ static::CDNURL ]);
                $linked[] = "<{$source}>; rel=preload; as=style";
                $this->addLink($source, 'stylesheet', Style::FORMAT);
            }

            if (!empty($google->file)) {

                $source = $google->build('https://fonts.googleapis.com/css');
                $linked[] = "<{$source}>; rel=preload; as=style";
                $this->addLink($source, 'stylesheet', Font::FORMAT);
            }
        }

        // JS

        if (isset($option[Script::MINIFY])) {

            $bundle = new Script();
            $inline = new Script();
            $filter = '//script[not(@type) or @type="text/javascript"]';

            foreach ($schema->query($filter) as $object) {

                $object->parentNode->removeChild($object);

                if ($source = $object->getAttribute('src')) {

                    $domain = wp_parse_url($source, PHP_URL_HOST) ?: __DOMAIN__;

                    if (($domain === __DOMAIN__) && $bundle->valid($source)) {

                        $bundle->import($source);
                    }
                    else {

                        $this->body->appendChild($object);
                        $object->setAttribute('defer', NULL);
                        $linked[] = "<{$source}>; rel=preload; as=script";
                    }
                }
                else if ($source = trim($object->textContent)) {

                    if ($bundle->valid($source)) {
                        $bundle->append($source);
                    }
                    else {
                        $inline->append($source);
                    }
                }
            }

            if (!empty($bundle->code)) {

                $source = $bundle->cache($option[static::CDNURL]);
                $linked[] = "<{$source}>; rel=preload; as=script";
                $this->addScript($source, 'defer', Script::FORMAT);
            }

            if (!empty($inline->code)) {

                $object = $this->createElement('script');
                $object->setAttribute('type', Script::FORMAT);
                $object->nodeValue = implode("\n", $inline->code);
                $this->body->appendChild($object);
            }
        }

        // IMG

        if (isset($option[Image::SRCSET])) {

            $cdndir = wp_parse_url(__CDNURL__, PHP_URL_PATH);
            $filter = "/html/body//img[contains(@src, '{$cdndir}')]";

            foreach ($schema->query($filter) as $object) {

                $bundle = new Image($object->getAttribute('src'));
                $pixels = $object->getAttribute('width') ?: $bundle->width;

                if ($bundle = $bundle->sizes($option[static::CDNURL])) {

                    $object->setAttribute('src', array_shift($bundle));

                    if (!empty($bundle)) {

                        $object->setAttribute('srcset', implode(',', $bundle));
                        $object->setAttribute('sizes' , "(max-width: {$pixels}px) 100vw, {$pixels}px");
                    }
                }
            }
        }

        // DOM

        if (isset($option[static::MINIFY])) {

            if ($object = @$this->meta['viewport']) {

                $values = preg_split('/\s*,\s*/', $object->getAttribute( 'content' ));
                $values = array_diff($values, ['maximum-scale=1.0', 'user-scalable=0']);
                $object->setAttribute('content', implode(', ', $values));
            }

            foreach ($schema->query('//comment()') as $object) {
                $object->parentNode->removeChild($object);
            }

            $this->formatOutput = false;
            $string = preg_replace('/>\s+</', '><', $this->saveHTML());
        }
        else {
            $string = $this->saveHTML();
        }

        count($linked) && header('Link: ' . implode(',', $linked));
        return $string;
    }


    function cache($string, $option) {

        if ($string = $this->build($string, $option)) {

            $handle = sprintf('~%s.html', md5($this->route));

            file_put_contents($target = __CDNDIR__ . $handle, $string);
            file_put_contents($target . '.gz' , gzencode($string , 9));
        }

        return $string;
    }


    function serve($accept) {

        $source = sprintf(__CDNDIR__ . '~%s.html', md5($this->route));

        if ($encode = $accept && (strpos($accept, 'gzip') !== false)) {
            $source .= '.gz';
        }

        if (file_exists($source) && time() - filemtime($source) < 600) {

            header('Vary: Accept-Encoding, Cookie');
            $encode && header('Content-Encoding: gzip');
            @readfile($source) && exit();
        }
    }


    static function encap($result) {

        return array_map(function($object) {

            return new static($object);

        }, $result ?: [ ]);
    }


    // ACTIONS

    static function countCache() {
        return count(glob(__CDNDIR__ . '~*.{html}', GLOB_BRACE));
    }


    static function cleanCache() {
        return count(array_filter(glob(__CDNDIR__ . '~*.{html,html.gz}', GLOB_BRACE), 'unlink')) / 2;
    }
}

?>