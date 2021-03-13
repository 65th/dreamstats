<?php

namespace Dreamstats\Model;
class User
{

    public static $users = [
        'Awake' => [
            'username' => 'Awake',
            'password' => '$2y$12$DgNH9l1VBn/7gVXTGEtCXeTKoo3piylEdcBgqoJeMBMwlJiiZVCP2',
            'admin' => true
        ]
    ];

    public $username;
    public $password;
    public $admin;
    public $authorized;

    private static $currentUser;

    /**
     * User constructor.
     * @param $username
     * @param $password
     * @param $admin
     * @param $authorized
     */
    public function __construct($username, $password, $admin, $authorized)
    {
        $this->username = $username;
        $this->password = $password;
        $this->admin = $admin;
        $this->authorized = $authorized;
    }


    public static function getCurrentUser()
    {
        if (!self::$currentUser) {
            $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
            $password = isset($_SESSION['password']) ? $_SESSION['password'] : null;

            if (!empty(self::$users[$username]) && password_verify($password, self::$users[$username]['password'])) {
                $admin = self::$users[$username]['admin'];
                $authorized = true;
            } else {
                $admin = false;
                $authorized = false;
            }

            self::$currentUser = new User($username, $password, $admin, $authorized);
        }

        return self::$currentUser;
    }

}