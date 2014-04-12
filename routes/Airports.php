<?php

$app->group('/Airports', function() use ($app) {

    $auth   = $app->deps['auth'];
    $orm    = $app->deps['orm'];
    $geo    = $app->deps['geo'];

    $app->get('/', function() use ($app, $auth, $orm, $geo) {
        //$auth->check($app, 'Airports', 'List');
        
        $raw    = false;
        $lat    = $app->request->params('lat');
        $lon    = $app->request->params('lon');
        $query  = $app->request->params('q');
        $limit  = $app->request->params('limit');

        try {
            if ($geo->isLat($lat) && $geo->isLon($lon)) {
                $sql = "
                    SELECT id, code, name, city, state, country, lat, lon, active, date_added, date_updated,
                    ROUND(( 3959 * acos( cos( radians(:lat) ) * cos( radians( lat ) ) * cos( radians( lon ) - radians(:lon) ) + sin( radians(:lat) ) * sin( radians( lat ) ) ) ), 3) AS distance 
                    FROM airport
                    WHERE date_archived IS NULL
                    ORDER BY distance
                ";
                $params = array("lat"=>$lat, "lon"=>$lon);
                $raw = true;
            } 
            
            if (!is_null($query)) {

            }

            if (is_numeric($limit) && $limit > 0) {
                $limit  = $limit <= 20 ? (int)$limit : 20;
                $sql    = $sql . " LIMIT $limit";
            }

            if ($raw) {
                $airports = $orm::forTable('airport')->rawQuery($sql, $params)->findArray();
            } else {
                $airports = $orm::forTable('airport')->limit($limit)->findArray();
            }

            $app->response->setStatus(200);
            $app->response->headers->set('Content-Type', 'application/json');
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


    $app->get('/:code/Terminals', function($code) use ($app, $auth, $orm) {

        try {
            $terminals = $orm::forTable('place')->select('airport_code')->select('terminal')->selectExpr('COUNT(id)', 'places')->where('airport_code', $code)->whereNotEqual('terminal', '')->groupBy('terminal')->orderByAsc('terminal')->findArray();
            
            $app->response->setStatus(200);
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->write(json_encode($terminals));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });

    $app->get('/:code/Terminals/:terminal/Places', function($code, $terminal) use ($app, $auth, $orm) {

        try {
            $places = $orm::forTable('place')->where('airport_code', $code)->where('terminal', $terminal)->orderByExpr('gate + 1')->findArray();
            
            $app->response->setStatus(200);
            $app->response->headers->set('Content-Type', 'application/json');
            $app->response->write(json_encode($places));

        } catch (Exception $e) {

            $app->response->setStatus(400);
            $app->response->headers->set('Error', $e->getMessage());
        }
    });
});
