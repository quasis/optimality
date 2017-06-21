<?php

namespace optimality;

class IM extends \WP_Image_Editor_Imagick {

    protected function strip_meta() {

        $this->image->stripImage();
        return true;
    }


    protected function _save($object, $target = null, $format = null) {

        list($target, $extens, $format) = $this->get_output_format($target, $format);
        if (!$target) $target = $this->generate_filename(null, null, $extens);

        $object->setOption('filter:support', '2.0');
        $object->setColorspace(\Imagick::COLORSPACE_SRGB);

        switch ($format) {

            case 'image/jpeg':
                $object->setOption('jpeg:fancy-upsampling', 'off');

                $object->setImageProperty('jpeg:dct-method'     , 'float');
                $object->setImageProperty('jpeg:sampling-factor', '4:2:0');
                $object->setSamplingFactors(['2x2', '1x1', '1x1']);

                if (strlen($object->getImageBlob()) > 10240) {
                    $object->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
                }

                break;

            case 'image/png':
                $object->setOption('png:compression-filter'  ,   '5');
                $object->setOption('png:compression-level'   ,   '9');
                $object->setOption('png:compression-strategy',   '0');
                $object->setOption('png:exclude-chunk'       , 'all');

                $object->setImageDepth(8);
                $object->setImageType(\Imagick::IMGTYPE_PALETTE);
                $object->setInterlaceScheme(\Imagick::INTERLACE_NO);

                if ($object->getImageAlphaChannel() == \Imagick::ALPHACHANNEL_UNDEFINED) {
                    $object->setImageAlphaChannel(\Imagick::ALPHACHANNEL_OPAQUE);
                }

                break;

            case 'image/gif':
                break;

            case 'image/webp':
                break;
        }

        return parent::_save($object, $target, $format);
    }
}

?>
