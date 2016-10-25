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

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Twig extension to create a link for arround text starting with issue{number}
 * or iss{number}.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class IssueLinkExtension extends \Twig_Extension
{
    private $generator;

    private $routeName;

    private $routeParameters;

    private $routeRelative;

    private $requestStack;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'versioncontrol_issuelink';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('issueLink', array($this, 'issueLink')),
        );
    }

    /**
     * @param string $text
     * @param int    $projectId
     *
     * @return string
     */
    public function issueLink($text, $projectId)
    {
        $matches = array();
        if (preg_match('/(issue|iss|issu)(\d+)/i', $text, $matches)) {
            foreach ($matches as $issueId) {
                if (is_numeric($issueId)) {
                    $issueUrl = $this->generateIssueUrl($issueId, $projectId);

                    return '<a href="'.$issueUrl.'"  data-remote="false" data-toggle="modal" data-target="#issueModal" title="Related to issue'.$issueId.'" class="ajax-modal non-ajax">'.$text.'</a>';
                }
            }
        }

        return $text;
    }

        /**
         * Gerenates a url.
         *
         * @param int $value Page paramater value
         *
         * @return string Url
         */
        protected function generateIssueUrl($issueId, $projectId, $routeName = 'issue_show_modal', $issueParamaterName = 'issueId')
        {
            $parameters = array($issueParamaterName => $issueId, 'id' => $projectId);

            return $this->generator->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
        }
}
