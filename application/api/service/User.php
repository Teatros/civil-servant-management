<?php
/**
 * Created by PhpStorm.
 * User: 沁塵
 * Date: 2019/2/19
 * Time: 11:22
 */

namespace app\api\service;

use app\api\model\User as UserModel;
use app\api\service\token\LoginToken;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class User
{

    private $loginTokenService;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->loginTokenService = LoginToken::getInstance();
    }
    /**
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public static function getUserByUserName($username) {
        return UserModel::where('name',$username)->find();
    }

    /**
     * @throws \Exception
     */
    public function generateToken($user) {
        $token = $this->loginTokenService->getToken($user);
        return $token;
    }

}