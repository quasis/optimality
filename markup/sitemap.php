<?php

namespace optimality;

class Sitemap extends \DOMDocument {

    const   PATH = '/sitemap.xml';
    const   GOOG = 'http://www.google.com/webmasters/tools/ping?sitemap=';
    const   BING = 'http://www.bing.com/ping?sitemap=';

    public $root;
    public $path;


    function __invoke($target, $option) {
        $this->path = array(
            __CDNURL__  => $option[Html::CDNURL],
        );
    }


    function addNode($entity) {

        $parent = $this->createElement('url');
        $this->root->appendChild($parent);

        $branch = $this->createElement('loc');
        $branch->nodeValue = $entity->route;
        $parent->appendChild($branch);

        if ($entity->edit) {

            $branch = $this->createElement('lastmod');
            $branch->nodeValue = $entity->edit;
            $parent->appendChild($branch);
        }

        if ($entity->image) {

            $branch = $this->createElement('image:image');

            $source = $this->createElement('image:loc');
            $source->nodeValue = strtr($entity->image, $this->path);
            $branch->appendChild($source);

            if ($entity->name) {

                $header = $this->createElement('image:caption');
                $header->nodeValue = $entity->name;
                $branch->appendChild($header);
            }

            $parent->appendChild($branch);
        }
    }


    function build($string, $option) {

        $this->loadXML('<urlset/>', LIBXML_COMPACT|LIBXML_NOBLANKS);

        $this->root = $this->documentElement;
        $this->root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->root->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');

        if (isset($option[Site::SEMETA])) {
            array_map([$this, 'addNode'], Site::fetch());
        }

        if (isset($option[Page::SEMETA])) {
            array_map([$this, 'addNode'], Page::fetch());
        }

        if (isset($option[Post::SEMETA])) {
            array_map([$this, 'addNode'], Post::fetch());
        }

        if (isset($option[Section::SEMETA])) {
            array_map([$this, 'addNode'], Section::fetch());
        }

        if (isset($option[Term::SEMETA])) {
            array_map([$this, 'addNode'], Term::fetch());
        }

        if (isset($option[User::SEMETA]) && !isset($option[User::UNLINK])) {
            array_map([$this, 'addNode'], User::fetch());
        }

        if (isset($option[Media::SEMETA]) && !isset($option[Media::UNLINK])) {
            array_map([$this, 'addNode'], Media::fetch());
        }

        $this->version = '1.0';
        $this->encoding = get_bloginfo('charset') ?: 'UTF-8';
        $this->formatOutput = false;

        http_response_code(200);
		header("Content-Type: text/xml;charset={$this->encoding}", true);
		header('X-Robots-Tag: noindex, follow', true);
        return $this->saveXML();
    }


    function cache($string, $option) {
        return $this->build($string, $option);
    }


    function serve($accept) {
        return false;
    }


    static function ping() {

        wp_remote_get(static::GOOG . urlencode(home_url(static::PATH)), ['blocking' => false]);
        wp_remote_get(static::BING . urlencode(home_url(static::PATH)), ['blocking' => false]);
    }
}

?>
