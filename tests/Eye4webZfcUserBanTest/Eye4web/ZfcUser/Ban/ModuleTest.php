<?php

namespace Eye4webZfcUserBanTest\Eye4web\ZfcUser\Ban;


use Eye4web\ZfcUser\Ban\Entity\UserBannableInterface;
use Zend\Mvc\MvcEvent;
use Eye4web\ZfcUser\Ban\Module;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    protected $module;

    protected $eventManager;

    protected $authService;

    protected $serviceManager;

    protected $application;

    protected $user;

    protected $mvcEvent;

    public function setUp()
    {
        $this->eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $this->authService = $this->getMock('Zend\Authentication\AuthenticationServiceInterface');

        $this->serviceManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $this->application = $this->getMockBuilder('Zend\Mvc\Application')
            ->disableOriginalConstructor()
            ->getMock();

        $this->user = $this->getMock('Eye4web\ZfcUser\Ban\Entity\UserBannableInterface');

        $this->mvcEvent = $this->getMock('Zend\Mvc\MvcEvent');

        $this->module = new Module();
    }

    public function testOnBootstrap()
    {

        $this->mvcEvent->expects($this->once())
            ->method('getApplication')
            ->will($this->returnValue($this->application));

        $this->application->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($this->eventManager));

        $this->eventManager->expects($this->once())
            ->method('attach')
            ->with(MvcEvent::EVENT_DISPATCH, array($this->module, 'checkIsBanned'), 1);

        $this->module->onBootstrap($this->mvcEvent);
    }

    /**
     * @dataProvider checkBanDataProvider
     */
    public function testCheckIsBanned($userLoggedIn, $bannableInterface, $banned, $route)
    {
        $this->application->expects($this->once())
            ->method('getServiceManager')
            ->will($this->returnValue($this->serviceManager));

        $this->serviceManager->expects($this->any())
            ->method('get')
            ->with('zfcuser_auth_service')
            ->will($this->returnValue($this->authService));

        $this->mvcEvent->expects($this->once())
            ->method('getApplication')
            ->will($this->returnValue($this->application));

        $this->authService->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue($userLoggedIn));

        if ($userLoggedIn) {

            $this->authService->expects($this->once())
                ->method('getIdentity')
                ->will($this->returnValue($this->user));

            if ($bannableInterface) {
                $this->user->expects($this->once())
                    ->method('getIsBanned')
                    ->will($this->returnValue($banned));

                if ($banned) {
                    $routeMatch = $this->getMockBuilder('Zend\Mvc\Router\RouteMatch')
                        ->disableOriginalConstructor()
                        ->getMock();
                    $routeMatch->expects($this->once())
                        ->method('getMatchedRouteName')
                        ->will($this->returnValue($route));

                    $this->mvcEvent->expects($this->any())
                        ->method('getRouteMatch')
                        ->will($this->returnValue($routeMatch));

                    if ($route !== 'eye4web_zfcuser_ban') {
                        $response = $this->getMockBuilder('Zend\Stdlib\ResponseInterface')
                            ->disableOriginalConstructor()
                            ->getMock();

                        $redirectPlugin = $this->getMockBuilder('Zend\Mvc\Controller\Plugin\Redirect')
                            ->disableOriginalConstructor()
                            ->getMock();
                        $redirectPlugin->expects($this->once())
                            ->method('toRoute')
                            ->with('eye4web_zfcuser_ban')
                            ->will($this->returnValue($response));

                        $controller = $this->getMockBuilder('\Zend\Mvc\Controller\AbstractActionController')
                            ->disableOriginalConstructor()
                            ->getMock();
                        $controller->expects($this->once())
                            ->method('plugin')
                            ->with('redirect')
                            ->will($this->returnValue($redirectPlugin));

                        $this->mvcEvent->expects($this->once())
                            ->method('getTarget')
                            ->will($this->returnValue($controller));
                        $this->mvcEvent->expects($this->once())
                            ->method('stopPropagation');
                    }
                }
            }
        } else {
            $this->authService->expects($this->never())
                ->method('getIdentity');
        }

        $result = $this->module->checkIsBanned($this->mvcEvent);

        if ($userLoggedIn && $bannableInterface && $banned && $route !== 'eye4web_zfcuser_ban') {
            $this->assertSame($response, $result);
        }
    }

    public function checkBanDataProvider()
    {
        return [
            /* $userLoggedIn, $bannableInterface, $banned */
            [false, true, false, 'home'],
            [false, true, false, 'eye4web_zfcuser_ban'],
            [false, true, true, 'home'],
            [false, true, true, 'eye4web_zfcuser_ban'],
            [true, true, false, 'home'],
            [true, true, false, 'eye4web_zfcuser_ban'],
            [true, true, true, 'home'],
            [true, true, true, 'eye4web_zfcuser_ban'],
            [false, false, false, 'home'],
            [false, false, false, 'eye4web_zfcuser_ban'],
            [false, false, true, 'home'],
            [false, false, true, 'eye4web_zfcuser_ban'],
            [true, false, false, 'home'],
            [true, false, false, 'eye4web_zfcuser_ban'],
            [true, false, true, 'home'],
            [true, false, true, 'eye4web_zfcuser_ban'],
        ];
    }

    public function testGetConfig()
    {
        $result = $this->module->getConfig();
        $this->assertTrue(is_array($result));
    }
}
