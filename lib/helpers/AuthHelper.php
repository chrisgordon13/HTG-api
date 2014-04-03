<?php

class AuthHelper
{
    public function check($app, $section, $action)
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            $app->halt(401);
        }

        $orm = $app->deps['orm'];

        if (!$auth = $orm::forTable('auth')->findOne($_SERVER['PHP_AUTH_USER'])) {
            $app->halt(403);
        }

        $app->org_id = $auth->org_id;
    }
}
