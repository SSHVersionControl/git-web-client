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
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Twig extension to provide pagination.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class PaginationExtension extends \Twig_Extension
{
    protected $midRange = 5;

    protected $numPages = 0;

    protected $currentPage = 0;

    protected $pageIdentifier = 'page';

    private $generator;

    private $routeName;

    private $routeParameters;

    private $routeRelative;

    private $requestStack;

    public function __construct(UrlGeneratorInterface $generator, RequestStack $requestStack)
    {
        $this->generator = $generator;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'lre_pagination';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
                new \Twig_SimpleFilter('pagination', array($this, 'paginationFilter')),
            );
    }

    /**
     * This will NOT remove any trailing dots, i.e. won't change "There must be something more...".
     *
     * @param string $value Text possibly containing a trailing dot
     *
     * @return string Text with trailing dot added if there was none before
     *
     * @throws \InvalidArgumentException If {@code $value} is not a string
     */
    public function paginationFilter($totalRecords, $currentPage, $recordsPerPage = 30, $routeName = false, $routeParameters = array(), $routeRelative = false)
    {
        $this->routeName = $routeName;
        $this->routeParameters = $routeParameters;
        $this->routeRelative = $routeRelative ? (UrlGeneratorInterface::RELATIVE_PATH) : UrlGeneratorInterface::ABSOLUTE_URL;

        if ($totalRecords > 0) {
            $this->numPages = ceil($totalRecords / $recordsPerPage);
            $this->currentPage = $currentPage;
            $content = '<nav><ul class="pagination">'.$this->firstPage().$this->prevPage().$this->listPages().$this->nextPage().$this->lastPage().'</ul></nav>';
        } else {
            $content = '';
        }

        return $content;
    }

    protected function prevPage()
    {
        if ($this->currentPage > 1) {
            $prevPage = ($this->currentPage - 1);

            return '<li><a href="'.$this->generateUrl($prevPage).'" class="prev">prev</a></li>';
        }
    }
    protected function nextPage()
    {
        if ($this->currentPage < $this->numPages) {
            $nextPage = $this->currentPage + 1;

            return '<li><a href="'.$this->generateUrl($nextPage).'" class="next">next</a></li>';
        }
    }
    protected function firstPage()
    {
        $firstPage = '';
        if ($this->currentPage > ($this->midRange + 1)) {  //  if number of pages between "currentPage" and "firstPage" exceeds $midRange with 1...
                $firstPage .= '<li><a href="'.$this->generateUrl(1).'" class="first">First Page</a></li>';  //  ...show "first page"-link
                if ($this->currentPage > ($this->midRange + 2)) {   //  if number of pages between $currentPage and "first page" exceeds $midRange with more than 1
                    //$firstPage .= '&hellip;';  //  add "..." between "1st page"-link and first page in $range
                }
        }

        return $firstPage;
    }
    protected function lastPage()
    {
        $lastPage = '';
        if ($this->currentPage < ($this->numPages - $this->midRange)) {  //  if number of pages between "currentPage" and "last page" is equal to $midRange
                if (($this->currentPage < ($this->numPages - $this->midRange) - 1)) {  //  if number of pages between $currentPage and "last page" exceeds $range with more than two
                    //$lastPage .= '&hellip;';  //  add "..." between "last page"-link and last page in $range
                }
            $lastPage .= '<li><a href="'.$this->generateUrl($this->numPages).'" class="last">'.$this->numPages.'</a></li>';   //  show "last page"-link
        }

        return $lastPage;
    }

        //  Range of pages between (prev first ...) and (... last next)
        protected function listPages()
        {
            $listPages = '';
            for ($i = ($this->currentPage - $this->midRange); $i < (($this->currentPage + $this->midRange) + 1); ++$i) {
                if (($i > 0) && ($i <= $this->numPages)) {  //  if page number are within page range
                  if ($i == $this->currentPage) {
                      $listPages .= '<li class="active"><a href="'.$this->generateUrl($i).'">'.$i.'</a></li>';
                  }  //  if we're on current page
                  else {
                      $listPages .= '<li><a href="'.$this->generateUrl($i).'">'.$i.'</a></li>';
                  }  //  if not current page
                }
            }

            return $listPages;
        }

        /**
         * Gerenates a url.
         *
         * @param int $value Page paramater value
         *
         * @return string Url
         */
        protected function generateUrl($value)
        {
            $request = $this->requestStack->getCurrentRequest();

            $routeName = $this->routeName ? $this->routeName : $request->attributes->get('_route');
            $routeParameters = $request->attributes->get('_route_params');

            if (key_exists('searchstate', $routeParameters)) {
                unset($routeParameters['searchstate']);
            }
            //Adds page param
            $routeParameters[$this->pageIdentifier] = $value;

            $parameters = array_merge($routeParameters, $this->routeParameters);

            return $this->generator->generate($routeName, $parameters, $this->routeRelative);
        }
}
