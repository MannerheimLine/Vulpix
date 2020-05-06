<?php


namespace Vulpix\Engine\RBAC\Actions;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vulpix\Engine\RBAC\Domains\Permission;
use Vulpix\Engine\RBAC\Domains\RBACExceptionsHandler;
use Vulpix\Engine\RBAC\Responders\PermissionsGetResponder;

/**
 * Класс вернет все привелегии системы, разделенные на группы
 *
 * Class PermissionsGetAction
 * @package Vulpix\Engine\RBAC\Actions
 */
class PermissionsGetAction implements RequestHandlerInterface
{
    private $_permission;
    private $_responder;

    /**
     * PermissionsGetDiffAction constructor.
     * @param Permission $permission
     * @param PermissionsGetResponder $responder
     */
    public function __construct(Permission $permission, PermissionsGetResponder $responder)
    {
        $this->_permission = $permission;
        $this->_responder = $responder;
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try{
            $permissions = $this->_permission->getAll();
            $response = $this->_responder->respond($request, $permissions);
            return $response;
        }catch (\Exception $e){
            return (new RBACExceptionsHandler())->handle($e);
        }
    }
}
