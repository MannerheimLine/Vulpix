<?php

declare(strict_types = 1);

namespace Vulpix\Engine\AAIS\Domains;

use Vulpix\Engine\AAIS\Service\JWTCreator;
use Vulpix\Engine\AAIS\Service\RTCreator;
use Vulpix\Engine\Core\DataStructures\Entity\HttpResultContainer;
use Vulpix\Engine\Core\Utility\Sanitizer\Sanitizer;
use Vulpix\Engine\Database\Connectors\IConnector;

/**
 * Аутентификация - процесс проверки учетных данных пользователя.
 *
 * Class Authentication
 * @package Vulpix\Engine\AAIS\Domains
 */
class Authentication
{
    private $_dbConnector;
    private $_rtCreator;
    private $_resultContainer;

    /**
     * Authentication constructor.
     * @param IConnector $dbConnector
     * @param RTCreator $rtCreator
     * @param HttpResultContainer $resultContainer
     */
    public function __construct(IConnector $dbConnector, RTCreator $rtCreator, HttpResultContainer $resultContainer)
    {
        $this->_dbConnector = $dbConnector;
        $this->_rtCreator = $rtCreator;
        $this->_resultContainer = $resultContainer;
    }

    /**
     * Проверяет наличие учетной записи пользователя, для дальнейшей авторизации
     *
     * @param string $userName
     * @return bool|mixed
     */
    private function findAccount(string $userName){
        $query = ("SELECT `id` as `userId`, `user_name` as `userName`, `password_hash` FROM `user_accounts` WHERE `user_name` = :userName");
        $result = $this->_dbConnector::getConnection()->prepare($query);
        $result->execute([
            'userName' => $userName
        ]);
        if ($result->rowCount() > 0){
            return $accountData = $result->fetch();
        }
        return false;
    }

    /**
     * Проводит аутентификацию пользователя по заданным параметрам.
     * Может отвечать сообщениями с необходимой информацией об ошибке аутентификации.
     * В случае успеха вернет пару из access / refresh tokens + expire time for access token.
     *
     * @param string|null $userName
     * @param string|null $userPassword
     * @return HttpResultContainer
     */
    public function authenticate(? string $userName, ? string $userPassword) : HttpResultContainer {
        /**
         * Санитизация нужна только для имени пользователя, так как только этот параметр используется в обращении к БД.
         */
        $userName = Sanitizer::sanitize($userName);
        $accountDetails = $this->findAccount($userName);
        if ($accountDetails !== false){
            $hash = $accountDetails['password_hash'];
            if (password_verify($userPassword, $hash)){
                $tokens = [
                    'accessToken' => JWTCreator::create($accountDetails),
                    'refreshToken' => $this->_rtCreator->create($accountDetails),
                    /**
                     * По факту в клиент будет улетать время окончания на минуту меньше чем на самом деле.
                     */
                    'expiresIn' => JWTCreator::getExpiresIn() - 60
                ];
                return $this->_resultContainer->setBody($tokens)->setStatus(200);
            }
            return $this->_resultContainer->setBody('Пароль не верен.')->setStatus(403);
        }
        return $this->_resultContainer->setBody('Такой учетной записи не существует в системе')->setStatus(403);
    }

}