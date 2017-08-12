<?php
namespace Restgrip\RouteAcl\Service;

use Phalcon\Acl;
use Phalcon\Acl\Adapter;
use Restgrip\Router\Route;
use Restgrip\Router\Router;
use Restgrip\Service\ServiceAbstract;

/**
 * @package   Restgrip\RouteAcl\Service
 * @author    Sarjono Mukti Aji <me@simukti.net>
 */
class RouteAclService extends ServiceAbstract
{
    /**
     * @var Adapter
     */
    protected $adapter;
    
    /**
     * Current role
     *
     * @var string
     */
    protected $role;
    
    /**
     * @param Adapter|null $adapter
     */
    public function __construct(Adapter $adapter = null)
    {
        if (!$adapter) {
            $adapter = new Adapter\Memory();
            $adapter->setDefaultAction(Acl::DENY);
        }
        
        $this->setAdapter($adapter);
    }
    
    /**
     * @return Adapter
     */
    public function getAdapter()
    {
        $evm = $this->getEventsManager();
        
        $this->adapter->setEventsManager($evm);
        // To avoid collision with acl events, don't use 'acl' event scope name.
        // @link https://docs.phalconphp.com/en/3.2/acl#events
        // Portability: roles definition can came from events subscriber.
        $evm->fire('routeAcl:beforeRouteAclCheckAccess', $this->adapter);
        
        return $this->adapter;
    }
    
    /**
     * @param Adapter $adapter
     *
     * @return $this
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getRole() : ?string
    {
        return $this->role;
    }
    
    /**
     * @param string $role
     *
     * @return $this
     */
    public function setRole(string $role)
    {
        $this->role = $role;
        
        return $this;
    }
    
    /**
     * Validate submitted token/user role against route role.
     *
     * @param Route       $route
     * @param string      $role
     * @param null|string $resource
     *
     * @return bool
     */
    public function isAllowed(Route $route, string $role, ?string $resource = null) : bool
    {
        $acl = $this->getAdapter();
        if (!$acl->isRole($route->getRole())) {
            $acl->addRole($route->getRole());
        }
        
        if (!$resource) {
            $resource = $route->getName();
        }
        
        $acl->addResource($route->getScope(), $route->getName());
        $acl->allow($route->getRole(), $route->getScope(), $route->getName());
        
        return $acl->isAllowed($role, $route->getScope(), $resource);
    }
    
    /**
     * Get list of allowed routes for submitted role.
     *
     * @param Router $router
     * @param string $role
     *
     * @return array
     */
    public function getResources(Router $router, string $role)
    {
        $acl  = $this->getAdapter();
        $data = [];
        
        foreach ($router->getRoutes() as $route) {
            if (!$route->isVisible()) {
                continue;
            }
            
            $routeRole  = $route->getRole() ?? '___NOROLE___';
            $routeScope = $route->getScope() ?? '___NOSCOPE___';
            
            if (!$acl->isRole($routeRole)) {
                $acl->addRole($route->getRole());
            }
            
            $acl->addResource($routeScope, $route->getName());
            $acl->allow($routeRole, $routeScope, $route->getName());
            
            if ($acl->isAllowed($role, $routeScope, $route->getName())) {
                $group          = $route->getGroupName() ?? '/';
                $data[$group][] = $route->getName();
            }
        }
        
        return $data;
    }
}