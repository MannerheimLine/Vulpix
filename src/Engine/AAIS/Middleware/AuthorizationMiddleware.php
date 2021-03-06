<?php

declare(strict_types = 1);

namespace Vulpix\Engine\AAIS\Middleware;

use Firebase\JWT\JWT;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vulpix\Engine\AAIS\Service\JWTCreator;
use Vulpix\Engine\AAIS\Service\AAISExceptionsHandler;

/**
 * Авторизация - проверка прав пользователя на ДОСТУП к определенным ресурсам.
 *
 * Class AuthorizationMiddleware
 * @package Vulpix\Engine\AAIS\Middleware
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try{
            /**
             * В случае если токен был передан в заголовках
             */
            if (!empty($request->getHeader('authorization'))){
                $accessToken = mb_substr(($request->getHeader('authorization')[0]), 7);
                $secretKey = JWTCreator::getSecretKey();
                $decoded = (JWT::decode($accessToken, $secretKey, ['HS256']))->user;
                $user['userId'] = $decoded->userId;
                $user['userName'] = $decoded->userName;
                $response = $handler->handle($request = $request->withAttribute('User', $user));
            }
            /**
             * Иначе клиент должен обработать 401 статус, перенаправив на авторизацию /auth/doAuth
             */
            else{
                $response = new JsonResponse('Access токен не найден в заголовке Authorization', 401);
                return $response->withHeader('Location', '/auth/doAuth');
            }
            return $response;
        }catch (\Exception $e){
             return  (new AAISExceptionsHandler())->handle($e);
        }
    }
}