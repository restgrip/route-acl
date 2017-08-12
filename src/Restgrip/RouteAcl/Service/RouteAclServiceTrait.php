<?php
namespace Restgrip\RouteAcl\Service;

use Phalcon\DiInterface;

/**
 * @method DiInterface getDI()
 * @package   Api\Acl\Acl
 * @author    Sarjono Mukti Aji <me@simukti.net>
 */
trait RouteAclServiceTrait
{
    /**
     * @return RouteAclService
     */
    public function getRouteAclService()
    {
        return $this->getDI()->getShared(RouteAclService::class);
    }
}