<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Eye4web\ZfcUser\Ban;

use Eye4web\ZfcUser\Ban\Entity\UserBannableInterface;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array($this, 'checkIsBanned'), 1);
    }

    public function checkIsBanned(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $authService = $serviceManager->get('zfcuser_auth_service');

        if ($authService->hasIdentity()) {
            $user = $authService->getIdentity();
            if ($user instanceof UserBannableInterface && $user->getIsBanned()) {
                if ($event->getRouteMatch()->getMatchedRouteName() !== 'eye4web_zfcuser_ban') {
                    $controller = $event->getTarget();
                    $response = $controller->plugin('redirect')->toRoute('eye4web_zfcuser_ban');
                    $event->stopPropagation();
                    return $response;
                }
            }
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../../../config/module.config.php';
    }
}
