<?php

declare(strict_types = 1);

namespace Vulpix\Engine\RBAC\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vulpix\Engine\Database\Connectors\IConnector;
use Vulpix\Engine\RBAC\Domains\RoleCollection;

class InitRolesMiddleware implements MiddlewareInterface
{
    private $_dbConnector;

    public function __construct(IConnector $dbConnector)
    {
        $this->_dbConnector = $dbConnector;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userId = 1;
        $roles = (new RoleCollection($this->_dbConnector))->initRoles($userId);
        $request = $request->withAttribute('Roles', $roles);
        return $response = $handler->handle($request);
    }
}