<?php


namespace Vulpix\Engine\RBAC\Responders;


use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class PermissionsGetDiffResponder
{
    public function respond(ServerRequestInterface $request, $payload = null): Response
    {
        return new JsonResponse($payload, 200);
    }
}
