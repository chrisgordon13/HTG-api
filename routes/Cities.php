<?php

$app->group('/Cities', function() use ($app) {

    $auth   = $app->deps['auth'];
    $orm    = $app->deps['orm'];

    $app->get('/', function() use ($app, $auth, $orm) {
        //$auth->check($app, 'Cities', 'List');

        try {
            $citys = $orm::forTable('city')->findArray();

            $app->response->write(json_encode($citys, JSON_PRETTY_PRINT));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->post('/', function() use ($app, $auth, $orm) {
        //$auth->check($app, 'Cities', 'Post');

        try {
            $body = json_decode($app->request->getBody());

            if (is_null($body)) {
                throw new Exception('Posted data is missing or malformed');
            }

            if (!property_exists($body, 'name') || $body->name == '') {
                throw new Exception('An cityanization name is required');
            }

            $city    = $orm::forTable('city')->create();

            $key    = $app->deps['key'];
            $id     = 'city-' . $key->create();

            $city->set('id', $id);
            $city->set('name', $body->name);
            $city->setExpr('date_added', 'NOW()');
            $city->setExpr('date_updated', 'NOW()');

            $city->save();

            $app->response->setStatus(201);
            $app->response->headers->set('Location', "Cities/$id");

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->get('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Cities', 'Get');

        try {
            $city = $orm::forTable('city')->findOne($id);

            $app->response->write(json_encode($city->asArray(), JSON_PRETTY_PRINT));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->put('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Cities', 'Put');

        try {

            $body = json_decode($app->request->getBody());

            if (is_null($body)) {
                throw new Exception('Posted data is missing or malformed');
            }

            $city = $orm::forTable('city')->findOne($id);

            if (property_exists($body, 'name') && $body->name != '') {
                $city->set('name', $body->name);
            }

            $city->setExpr('date_updated', 'NOW()');

            $city->save();

            $app->response->setStatus(204);

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->delete('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Cities', 'Delete');

        try {
            $city = $orm::forTable('city')->findOne($id);

            $city->setExpr('date_archived', 'NOW()');

            $city->save();

            $app->response->setStatus(204);

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });
});
