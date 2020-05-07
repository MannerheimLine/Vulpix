<?php

declare(strict_types = 1);

namespace Vulpix\Engine\AAIS\Domains;

use Laminas\Diactoros\Response\JsonResponse;
use Vulpix\Engine\Core\Utility\ExceptionsHandler\ExceptionsHandler;

/**
 * Расширяет возможности по обработке исключений брошенных Firebase\JWT.
 *
 * Class AAISExceptionsHandler
 * @package Vulpix\Engine\AAIS\Domains
 */
class AAISExceptionsHandler extends ExceptionsHandler
{

    /**
     * Обработка ошибки связанной с неверной подписью JWT.
     *
     * @return JsonResponse
     */
    private function handleSignatureInvalidException() : JsonResponse {
        /**
         * Если вдруг оказалось, что JWT токен имеет неверную подпись.
         * Клиентом перенаправить пользователя на повторную аутентификацию /auth/doAuth.
         */
        $response = new JsonResponse('Ошибка при проверке подписи токена',401);
        return $response->withHeader('Location', '/auth/doAuth');
    }

    /**
     * Обработка ошибки связанной с окончанием действия JWT.
     *
     * @return JsonResponse
     */
    private function handleExpiredException() : JsonResponse {
        /**
         * Если у JWT токена закончилось время жизни.
         * Здесь я должен перенаправить клиент на /auth/doRefresh.
         */
        $response = new JsonResponse('Закончилось время жизни Access токена',401);
        return $response->withHeader('Location', '/auth/doRefresh');
    }

    /**
     * Обработка ошибки связанной с неверными $header, $payload в JWT.
     *
     * @param \Exception $e
     * @return JsonResponse
     */
    private function handleWrongParamTypeException(\Exception $e) : JsonResponse {
        return (new JsonResponse($e->getMessage(),400));
    }

    /**
     * Обработка ошибки связанной с передачей empty, null параметров в метод.
     *
     * @param \Exception $e
     * @return JsonResponse
     */
    private function handleWrongAccessTokenException(\Exception $e){
        return (new JsonResponse($e->getMessage(),400));
    }

    /**
     * Фабричный метод для обработки всех возможных Exceptions.
     *
     * @param \Exception $e
     * @return JsonResponse
     */
    public function handle(\Exception $e) {
        $method = 'handle'.basename(get_class($e));
        if (method_exists($this, $method)){
            return $this->$method($e);
        }else{
            return $this->unhandled($e);
        }
    }

}