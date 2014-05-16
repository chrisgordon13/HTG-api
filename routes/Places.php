<?php

$app->group('/Places', function() use ($app) {

    $auth   = $app->deps['auth'];
    $orm    = $app->deps['orm'];

    $app->get('/', function() use ($app, $auth, $orm) {
        //$auth->check($app, 'Places', 'List');

        try {
            if ($app->request->get('type')) {
                $type = $app->request->get('type');
                $places = $orm::forTable('place')->where('category', $type)->findArray();
            } else {
                $places = $orm::forTable('place')->findArray();
            }

            $app->response->write(json_encode($places, JSON_PRETTY_PRINT));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->post('/', function() use ($app, $auth, $orm) {
        //$auth->check($app, 'Places', 'Post');

        try {
            $body = json_decode($app->request->getBody());

            if (is_null($body)) {
                throw new Exception('Posted data is missing or malformed');
            }

            if (!property_exists($body, 'name') || $body->name == '') {
                throw new Exception('An placeanization name is required');
            }

            $place    = $orm::forTable('place')->create();

            $key    = $app->deps['key'];
            $id     = 'place-' . $key->create();

            $place->set('id', $id);
            $place->set('name', $body->name);
            $place->setExpr('date_added', 'NOW()');
            $place->setExpr('date_updated', 'NOW()');

            $place->save();

            $app->response->setStatus(201);
            $app->response->headers->set('Location', "Places/$id");

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->get('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Places', 'Get');

        try {
            $place = $orm::forTable('place')->findOne($id);

            $app->response->write(json_encode($place->asArray(), JSON_PRETTY_PRINT));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->put('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Places', 'Put');

        try {

            $body = json_decode($app->request->getBody());

            if (is_null($body)) {
                throw new Exception('Posted data is missing or malformed');
            }

            $place = $orm::forTable('place')->findOne($id);

            if (property_exists($body, 'name') && $body->name != '') {
                $place->set('name', $body->name);
            }

            $place->setExpr('date_updated', 'NOW()');

            $place->save();

            $app->response->setStatus(204);

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->delete('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Places', 'Delete');

        try {
            $place = $orm::forTable('place')->findOne($id);

            $place->setExpr('date_archived', 'NOW()');

            $place->save();

            $app->response->setStatus(204);

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });
});
