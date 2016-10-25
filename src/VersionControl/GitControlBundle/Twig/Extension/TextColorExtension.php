<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Twig\Extension;

/**
 * Twig extension providing filters for hex color selection.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class TextColorExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'versioncontrol_textcolor';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('textcolor', array($this, 'textColor')),
        );
    }

    /**
     * @param string $colorHex
     *
     * @return string
     */
    public function textColor($colorHex)
    {

        //break up the color in its RGB components
        $r = hexdec(substr($colorHex, 0, 2));
        $g = hexdec(substr($colorHex, 2, 2));
        $b = hexdec(substr($colorHex, 4, 2));

        $contrast = sqrt(
            $r * $r * .241 +
            $g * $g * .691 +
            $b * $b * .068
        );

        if ($contrast > 130) {
            $color = '000000';
        } else {
            $color = 'FFFFFF';
        }

        return $color;
    }
}
