<?php
/*
 * This file is part of the GithubIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GithubIssueBundle\DataTransformer;

interface DataTransformerInterface
{
    public function transform($array);

    public function reverseTransform($entiy);
}
