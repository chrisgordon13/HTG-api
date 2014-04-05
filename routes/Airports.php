<?php

$app->group('/Airports', function() use ($app) {

    $auth   = $app->deps['auth'];
    $orm    = $app->deps['orm'];

    $app->get('/', function() use ($app, $auth, $orm) {
        //$auth->check($app, 'Airports', 'List');

        try {
            $airports = $orm::forTable('airport')->limit(20)->findArray();

            //print_r($airports);
            //$app->response->setStatus(200);
            //$app->response->headers->set('Content-Type', 'application/json');
            $app->response->write(json_encode($airports, JSON_PRETTY_PRINT));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->post('/', function() use ($app, $auth, $orm) {
        //$auth->check($app, 'Airports', 'Post');

        try {
            $body = json_decode($app->request->getBody());

            if (is_null($body)) {
                throw new Exception('Posted data is missing or malformed');
            }

            if (!property_exists($body, 'name') || $body->name == '') {
                throw new Exception('An airportanization name is required');
            }

            $airport    = $orm::forTable('airport')->create();

            $key    = $app->deps['key'];
            $id     = 'airport-' . $key->create();

            $airport->set('id', $id);
            $airport->set('name', $body->name);
            $airport->setExpr('date_added', 'NOW()');
            $airport->setExpr('date_updated', 'NOW()');

            $airport->save();

            $app->response->setStatus(201);
            $app->response->headers->set('Location', "Airports/$id");

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->get('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Airports', 'Get');

        try {
            $airport = $orm::forTable('airport')->findOne($id);

            $app->response->write(json_encode($airport->asArray(), JSON_PRETTY_PRINT));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->put('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Airports', 'Put');

        try {

            $body = json_decode($app->request->getBody());

            if (is_null($body)) {
                throw new Exception('Posted data is missing or malformed');
            }

            $airport = $orm::forTable('airport')->findOne($id);

            if (property_exists($body, 'name') && $body->name != '') {
                $airport->set('name', $body->name);
            }

            $airport->setExpr('date_updated', 'NOW()');

            $airport->save();

            $app->response->setStatus(204);

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->delete('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Airports', 'Delete');

        try {
            $airport = $orm::forTable('airport')->findOne($id);

            $airport->setExpr('date_archived', 'NOW()');

            $airport->save();

            $app->response->setStatus(204);

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });
});
