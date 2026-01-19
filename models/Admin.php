<?php

namespace app\models;

use Yii;
use yii\base\BaseObject;
use yii\web\IdentityInterface;

class Admin extends BaseObject implements IdentityInterface
{
    public $id = 1;
    public $username = 'admin';
    private $passwordHash = '$2y$10$jOTOoYr2z2o/y/8O4D/22uOSYeWruzz6WEsiFgpjSF7GkH5uamIzu'; // bcrypt for '123'

    public static function findIdentity($id)
    {
        return $id == 1 ? new self() : null;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null; // not used
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return false;
    }

    public static function findByUsername(string $username): ?self
    {
        return $username === 'admin' ? new self() : null;
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }
}
