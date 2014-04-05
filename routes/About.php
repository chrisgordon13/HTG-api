<?php

$app->group('/About', function() use ($app) {

    $auth   = $app->deps['auth'];
    $orm    = $app->deps['orm'];

    $app->get('/', function() use ($app, $auth, $orm) {
        //$auth->check($app, 'About', 'List');

        try {
            $abouts = $orm::forTable('about')->findArray();

            $app->response->write(json_encode($abouts, JSON_PRETTY_PRINT));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->post('/', function() use ($app, $auth, $orm) {
        //$auth->check($app, 'About', 'Post');

        try {
            $body = json_decode($app->request->getBody());

            if (is_null($body)) {
                throw new Exception('Posted data is missing or malformed');
            }

            if (!property_exists($body, 'name') || $body->name == '') {
                throw new Exception('An aboutanization name is required');
            }

            $about    = $orm::forTable('about')->create();

            $key    = $app->deps['key'];
            $id     = 'about-' . $key->create();

            $about->set('id', $id);
            $about->set('name', $body->name);
            $about->setExpr('date_added', 'NOW()');
            $about->setExpr('date_updated', 'NOW()');

            $about->save();

            $app->response->setStatus(201);
            $app->response->headers->set('Location', "About/$id");

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->get('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'About', 'Get');

        try {
            $about = $orm::forTable('about')->findOne($id);

            $app->response->write(json_encode($about->asArray(), JSON_PRETTY_PRINT));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->put('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'About', 'Put');

        try {

            $body = json_decode($app->request->getBody());

            if (is_null($body)) {
                throw new Exception('Posted data is missing or malformed');
            }

            $about = $orm::forTable('about')->findOne($id);

            if (property_exists($body, 'name') && $body->name != '') {
                $about->set('name', $body->name);
            }

            $about->setExpr('date_updated', 'NOW()');

            $about->save();

            $app->response->setStatus(204);

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->delete('/:id', function($id) use ($app, $auth, $orm) {
        //$auth->check($app, 'About', 'Delete');

        try {
            $about = $orm::forTable('about')->findOne($id);

            $about->setExpr('date_archived', 'NOW()');

            $about->save();

            $app->response->setStatus(204);

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });
});
