<?php
spl_autoload_register(function ($classname) {

    if (preg_match('/[a-zA-Z]+Helper$/', $classname)) {

        require $_SERVER['DOCUMENT_ROOT'] . '/lib/helpers/' . $classname . '.php';
        return true;

    } elseif (preg_match('/[a-zA-Z]+Exception$/', $classname)) {

        require $_SERVER['DOCUMENT_ROOT'] . '/lib/exceptions/' . $classname . '.php';
        return true;
    }
});
