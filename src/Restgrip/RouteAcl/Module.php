<?php
namespace Restgrip\RouteAcl;

use Restgrip\RouteAcl\Service\RouteAclService;
use Restgrip\Module\ModuleAbstract;

/**
 * @package   Restgrip\RouteAcl
 * @author    Sarjono Mukti Aji <me@simukti.net>
 */
class Module extends ModuleAbstract
{
    /**
     * @var array
     */
    protected $defaultServices = [
        RouteAclService::class,
    ];
}