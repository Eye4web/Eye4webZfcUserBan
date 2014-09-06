<?php

namespace Eye4webZfcUserBanTest\Eye4web\ZfcUser\Ban\Controller;

class IndexControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $authService;

    protected $controller;

    protected $pluginManager;

    protected $pluginManagerPlugins = array();

    protected $redirectPlugin;

    public function setUp()
    {
        $this->authService = $this->getMock('Zend\Authentication\AuthenticationServiceInterface');
        $this->pluginManagerPlugins['zfcUserAuthentication'] = $this->authService;

        $this->redirectPlugin = $this->pluginManager = $this->getMock('Zend\Mvc\Controller\Plugin\Redirect');
        $this->pluginManagerPlugins['redirect'] = $this->redirectPlugin;

        $this->pluginManager = $this->getMock('Zend\Mvc\Controller\PluginManager');
        $this->pluginManager->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(array($this, 'helperMockCallbackPluginManagerGet')));

        $this->controller = new \Eye4web\ZfcUser\Ban\Controller\IndexController();
        $this->controller->setPluginManager($this->pluginManager);
    }

    public function testIndexActionNotLoggedIn()
    {
        $this->authService->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue(false));

        $response = $this->getMock('Zend\Stdlib\ResponseInterface');

        $this->redirectPlugin->expects($this->once())
            ->method('toUrl')
            ->with('/')
            ->will($this->returnValue($response));

        $result = $this->controller->indexAction();

        $this->assertSame($response, $result);
    }

    public function testIndexActionNotBanned()
    {
        $user = $this->getMock('Eye4web\ZfcUser\Ban\Entity\UserBannableInterface');
        $user->expects($this->once())
            ->method('getIsBanned')
            ->will($this->returnValue(false));

        $this->authService->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue(true));

        $this->authService->expects($this->once())
            ->method('getIdentity')
            ->will($this->returnValue($user));

        $response = $this->getMock('Zend\Stdlib\ResponseInterface');

        $this->redirectPlugin->expects($this->once())
            ->method('toUrl')
            ->with('/')
            ->will($this->returnValue($response));

        $result = $this->controller->indexAction();

        $this->assertSame($response, $result);
    }

    public function testIndexActionBanned()
    {
        $user = $this->getMock('Eye4web\ZfcUser\Ban\Entity\UserBannableInterface');
        $user->expects($this->once())
            ->method('getIsBanned')
            ->will($this->returnValue(true));

        $this->authService->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue(true));

        $this->authService->expects($this->once())
            ->method('getIdentity')
            ->will($this->returnValue($user));

        $this->redirectPlugin->expects($this->never())
            ->method('toUrl');

        $result = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
    }

    public function helperMockCallbackPluginManagerGet($key)
    {
        return (array_key_exists($key, $this->pluginManagerPlugins))
            ? $this->pluginManagerPlugins[$key]
            : null;
    }
}
