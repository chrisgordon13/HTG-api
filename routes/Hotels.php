<?php

$app->group('/Hotels', function() use ($app) {

    $auth   = $app->deps['auth'];
    $orm    = $app->deps['orm'];

    $app->get('/', function() use ($app, $auth, $orm) {
        //$auth->check($app, 'Hotels', 'List');

        try {
            $hotels = $orm::forTable('hotel')->findArray();

            $app->response->write(json_encode($hotels, JSON_PRETTY_PRINT));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->post('/', function() use ($app, $auth, $orm) {
        //$auth->check($app, 'Hotels', 'Post');

        try {
            $body = json_decode($app->request->getBody());

            if (is_null($body)) {
                throw new Exception('Posted data is missing or malformed');
            }

            if (!property_exists($body, 'name') || $body->name == '') {
                throw new Exception('An hotelanization name is required');
            }

            $hotel    = $orm::forTable('hotel')->create();

            $key    = $app->deps['key'];
            $id     = 'hotel-' . $key->create();

            $hotel->set('id', $id);
            $hotel->set('name', $body->name);
            $hotel->setExpr('date_added', 'NOW()');
            $hotel->setExpr('date_updated', 'NOW()');

            $hotel->save();

            $app->response->setStatus(201);
            $app->response->headers->set('Location', "Hotels/$id");

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->get('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Hotels', 'Get');

        try {
            $hotel = $orm::forTable('hotel')->findOne($id);

            $app->response->write(json_encode($hotel->asArray(), JSON_PRETTY_PRINT));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->put('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Hotels', 'Put');

        try {

            $body = json_decode($app->request->getBody());

            if (is_null($body)) {
                throw new Exception('Posted data is missing or malformed');
            }

            $hotel = $orm::forTable('hotel')->findOne($id);

            if (property_exists($body, 'name') && $body->name != '') {
                $hotel->set('name', $body->name);
            }

            $hotel->setExpr('date_updated', 'NOW()');

            $hotel->save();

            $app->response->setStatus(204);

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->delete('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'Hotels', 'Delete');

        try {
            $hotel = $orm::forTable('hotel')->findOne($id);

            $hotel->setExpr('date_archived', 'NOW()');

            $hotel->save();

            $app->response->setStatus(204);

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });
});
