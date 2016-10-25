<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Annotation\Driver;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use VersionControl\GitControlBundle\Controller\Base\BaseProjectController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Annotation driver to check a user project access rights.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class ProjectAccessAnnotationDriver
{
    /**
     * Annotation Reader.
     *
     * @var Doctrine\Common\Annotations\Reader
     */
    private $reader;

    /**
     * @param Doctrine\Common\Annotations\Reader $reader
     */
    public function __construct($reader)
    {
        $this->reader = $reader;
    }

    /**
     * This event will fire during any controller call.
     *
     * @param FilterControllerEvent $event
     *
     * @return type
     *
     * @throws AccessDeniedHttpException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) { //return if no controller
            return;
        }

        $object = new \ReflectionObject($controller[0]); // get controller
        $method = $object->getMethod($controller[1]); // get method

        $configurations = $this->reader->getMethodAnnotations($method);

        foreach ($configurations as $configuration) { //Start of annotations reading

            if (isset($configuration->grantType) && $controller[0] instanceof BaseProjectController) {
                //Found our annotation
                $controller[0]->setProjectGrantType($configuration->grantType);
                $request = $controller[0]->get('request_stack')->getCurrentRequest();
                $id = $request->get('id', false);
                if ($id !== false) {
                    $redirectUrl = $controller[0]->initAction($id, $configuration->grantType);
                    if ($redirectUrl) {
                        $event->setController(
                            function () use ($redirectUrl) {
                                return new RedirectResponse($redirectUrl);
                            });
                    }
                }
            }
        }
    }
}
