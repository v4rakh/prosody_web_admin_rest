<?php

class ACL extends \Zend\Permissions\Acl\Acl
{
    public static $ROLE_GUEST = 'guest';
    public static $ROLE_MEMBER = 'member';
    public static $ROLE_ADMIN = 'admin';

    public static $ACL_RESOURCES = [
        '/401',
        '/403',
        '/404',
        '/500',
        '/',
        '/delete',
        '/password',
        '/verification/{verificationCode}',
        '/login',
        '/logout',
        '/signup',
    ];
    public static $ACL_ALLOWS = [
        'admin' => [''],
        'member' => [
            '/',
            '/delete',
            '/password',
            '/logout',
        ],
        'guest'  => [
            '/login',
            '/signup',
            '/verification/{verificationCode}',
            '/',
            '/401',
            '/403',
            '/404',
            '/500',
        ],
    ];
    public static $ACL_DENIES = [
        'admin'     => ['/login', '/signup', '/verification/{verificationCode}'],
        'member'    => ['/login', '/signup', '/verification/{verificationCode}'],
        'guest'     => []
    ];

    public function __construct()
    {
        $res = self::$ACL_RESOURCES;
        $allows = self::$ACL_ALLOWS;
        $denies = self::$ACL_DENIES;

        // roles
        $this->addRole(self::$ROLE_GUEST);
        $this->addRole(self::$ROLE_MEMBER, self::$ROLE_GUEST);
        $this->addRole(self::$ROLE_ADMIN);

        // resource
        foreach ($res as $resource) {
            $this->addResource($resource);
        }

        // allows
        foreach ($allows as $role => $paths) {

            foreach ($paths as $path) {

                if (empty($path) || $path === '') {
                    $this->allow($role);
                } else {
                    $this->allow($role, $path);
                }
            }
        }

        // denies
        foreach ($denies as $role => $paths) {

            foreach ($paths as $path) {

                if (empty($path) || $path === '') {
                    $this->deny($role);
                } else {
                    $this->deny($role, $path);
                }
            }
        }
    }
}