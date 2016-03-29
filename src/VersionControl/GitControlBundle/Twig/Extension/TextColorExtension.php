<?php

namespace VersionControl\GitControlBundle\Twig\Extension;

/**
 * Twig extension providing filters for locale-aware formatting of numbers and currencies.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2013 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class TextColorExtension extends \Twig_Extension {
    
    /**
     * {@inheritDoc}
     */
    public function getName() {
            return 'versioncontrol_textcolor';
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('textcolor', array($this, 'textColor')),
        );
    }

    /**
     * 
     * @param string $colorHex
     * @return string
     */
    public function textColor($colorHex) {

        //break up the color in its RGB components
        $r = hexdec(substr($colorHex,0,2));
        $g = hexdec(substr($colorHex,2,2));
        $b = hexdec(substr($colorHex,4,2));

        $contrast = sqrt(
            $r * $r * .241 +
            $g * $g * .691 +
            $b * $b * .068
        );

        if($contrast > 130){
            $color = '000000';
        }else{
            $color = 'FFFFFF';
        }

        return $color;
    }
     
}
