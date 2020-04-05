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

use VersionControl\GithubIssueBundle\Entity\User;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserToEntityTransformer implements DataTransformerInterface
{
    /**
     * Transforms an issue array into an issue Entity object.
     *
     * @param \VersionControl\GithubIssueBundle\Entity\Issues\User|null $user
     *
     * @return string
     */
    public function transform($user)
    {
        if (null === $user) {
            return null;
        }

        $userEntity = new User();
        $userEntity->setId($user['id']);
        $userEntity->setName($user['login']);

        return $userEntity;
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param VersionControl\GithubIssueBundle\Entity\Issues\User $user
     *
     * @return string|null label name
     *
     * @throws TransformationFailedException if object (issue) is not found
     */
    public function reverseTransform($userEntity)
    {
        if ($userEntity === null) {
            // causes a validation error
            throw new TransformationFailedException('userEntity is null');
        }

        return $userEntity->getName();
    }
}
