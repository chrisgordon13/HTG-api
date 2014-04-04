<?

$app->get('/', function() use ($app) {

    $links = [
        'Hotels'=>'/Hotes',
        'Places'=>'/Places'
    ];

    $app->response->write(json_encode($links));

});
