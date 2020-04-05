<?php

namespace VersionControl\GitControlBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Filesystem\Filesystem;

class BaseControllerTestCase extends WebTestCase
{
    protected $client = null;

    public $entityManager;

    protected $paths = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Creates an AuthorizedClient.
     */
    protected function createAuthorizedClient()
    {
        $container = $this->client->getContainer();

        $session = $container->get('session');
        /** @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $container->get('fos_user.user_manager');
        /** @var $loginManager \FOS\UserBundle\Security\LoginManager */
        $loginManager = $container->get('fos_user.security.login_manager');
        //$firewallName = $container->getParameter('fos_user.main');
        $firewallName = 'main';

        $user = $userManager->findUserBy(array('username' => 'test'));
        $loginManager->loginUser($firewallName, $user);

        // save the login token into the session and put it in a cookie
        $container->get('session')->set('_security_'.$firewallName,
            serialize($container->get('security.context')->getToken()));
        $container->get('session')->save();
        $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        return $user;
    }

    protected function doLogin($username, $password)
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('_submit')->form(array(
            '_username' => $username,
            '_password' => $password,
            ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
    }

    protected function createDatabase($username, $password)
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('_submit')->form(array(
            '_username' => $username,
            '_password' => $password,
            ));
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
    }

    public function getProject($user)
    {
        $firstUserProject = $this->entityManager->getRepository('VersionControlGitControlBundle:UserProjects')->findOneBy(array('user' => $user));

        return $firstUserProject->getProject();
    }

    protected function assertInputValue($crawler, $key, $val)
    {
        $input = $crawler->filter('input[name="'.$key.'"]');
        $this->assertEquals(
            $val, $input->attr('value'), $key.' failed'
        );
    }

    protected function assertSelectValue($crawler, $key, $val)
    {
        $input = $crawler->filter('select[name="'.$key.'"]');
        $selected_option = $input->filter('option[selected="selected"]');
        $this->assertEquals($val, $selected_option->attr('value'), $key.' failed');
    }

    protected function assertTextAreaValue($crawler, $key, $val)
    {
        $input = $crawler->filter('textarea[name="'.$key.'"]');
        $this->assertEquals(
            $val, $input->text(), $key.' failed'
        );
    }

    /**
     * Creates a temp folder. Use to create folder to test git functions.
     *
     * @param string $folderName
     */
    protected function createTempFolder($folderName)
    {
        $tempDir = realpath(sys_get_temp_dir());
        $tempFullPathName = tempnam($tempDir, $folderName);
        $this->paths[$folderName] = $tempFullPathName;
        @unlink($this->paths[$folderName]);
        $fs = new Filesystem();
        $fs->mkdir($this->paths[$folderName]);

        return $this->paths[$folderName];
    }

    /**
     * Remove any paths created after test.
     */
    protected function tearDown()
    {
        if (is_array($this->paths)) {
            $fs = new Filesystem();
            foreach ($this->paths as $path) {
                $fs->remove($path);
            }
        }
    }
}
