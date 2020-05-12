<?php

declare(strict_types = 1);

namespace Vulpix\Engine\RBAC\Responders;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Vulpix\Engine\Core\DataStructures\Entity\HttpResultContainer;

/**
 * Class RoleGetAllResponder
 * @package Vulpix\Engine\RBAC\Responders
 */
class RoleGetAllResponder
{
    /**
     * @param ServerRequestInterface $request
     * @param HttpResultContainer $payload
     * @return Response
     */
    public function respond(ServerRequestInterface $request, HttpResultContainer $payload): Response
    {
        return new JsonResponse($payload->getBody(), $payload->getStatus());
    }
}
