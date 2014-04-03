<?php                                                                                                                  
                                                                                                                       
$app->group('/Users', function() use ($app) {                                                                          
                                                                                                                       
    $auth   = $app->deps['auth'];                                                                                      
    $orm    = $app->deps['orm'];                                                                                       
                                                                                                                       
    $app->get('/', function() use ($app, $auth, $orm) {                                                                
        $auth->check($app, 'Users', 'List');                                                                           
                                                                                                                       
        try {                                                                                                          
            $users = $orm::forTable('user')->findArray();                                                              
                                                                                                                       
            $app->response->write(json_encode($users, JSON_PRETTY_PRINT));                                             
                                                                                                                       
        } catch (Exception $e) {                                                                                       
                                                                                                                       
            $app->response->setStatus(400);                                                                            
            $app->response->headers->set('Error', $e->getMessage());                                                   
        }                                                                                                              
    });                                                                                                                
                                                                                                                       
    $app->post('/', function() use ($app, $auth, $orm) {                                                               
        $auth->check($app, 'Users', 'Post');                                                                           
                                                                                                                       
        try {                                                                                                          
            $body = json_decode($app->request->getBody());                                                             
                                                                                                                       
            if (is_null($body)) {                                                                                      
                throw new Exception('Posted data is missing or malformed');                                            
            }                                                                                                          
                                                                                                                       
            if (!property_exists($body, 'org_id') || $body->org_id == '') {                                            
                throw new Exception('An organization id is required');                                                 
            }                                                                                                          
                                                                                                                       
            if (!property_exists($body, 'email') || $body->email = '') {                                               
                throw new Exception('An email address is required');                                                   
            }                                                                                                          
                                                                                                                       
            if (!property_exists($body, 'password') || $body->password = '') {                                         
                throw new Exception('A password is required');                                                         
            }                                                                                                          
                                                                                                                       
            $user   = $orm::forTable('user')->create();                                                                
                                                                                                                       
            $key    = $app->deps['key'];                                                                               
            $id     = 'site-' . $key->create();                                                                        
                                                                                                                       
            $user->set('id', $id);                                                                                     
            $user->set('org_id', $body->org_id);                                                                       
            $user->set('email', $body->email);                                                                         
            $user->set('password', crypt($body->password, $app->deps['salt']));                                        
            $user->setExpr('date_added', 'NOW()');                                                                     
            $user->setExpr('date_updated', 'NOW()');                                                                   
                                                                                                                       
            $user->save();                                                                                             
                                                                                                                       
            $app->response->setStatus(201);                                                                            
            $app->response->headers->set('Location', "Sites/$id");                                                     
                                                                                                                       
        } catch (Exception $e) {                                                                                       
                                                                                                                       
            $app->response->setStatus(400);                                                                            
            $app->response->headers->set('Error', $e->getMessage());                                                   
        }                                                                                                              
    });                                                                                                                
                                                                                                                       
    $app->get('/:id', function($id) use ($app, $auth, $orm) {                                                          
        $auth->check($app, 'Users', 'Get');                                                                            
                                                                                                                       
        try {                                                                                                          
            $user = $orm::forTable('user')->findOne($id);                                                              
                                                                                                                       
            $app->response->write(json_encode($user->asArray(), JSON_PRETTY_PRINT));                                   
                                                                                                                       
        } catch (Exception $e) {                                                                                       
                                                                                                                       
            $app->response->setStatus(400);                                                                            
            $app->response->headers->set('Error', $e->getMessage());                                                   
        }                                                                                                              
    });                                                                                                                
                                                                                                                       
    $app->put('/:id', function($id) use ($app, $auth, $orm) {                                                          
        $auth->check($app, 'Users', 'Put');                                                                            
                                                                                                                       
        try {                                                                                                          
            $body = json_decode($app->request->getBody());                                                             
                                                                                                                       
            if (is_null($body)) {                                                                                      
                throw new Exception('Posted data is missing or malformed');                                            
            }                                                                                                          
                                                                                                                       
            $user = $orm::forTable('user')->findOne($id);                                                              
                                                                                                                       
            if (property_exists($body, 'org_id') && $body->org_id != '') {                                             
                $user->set('org_id', $body->org_id);                                                                   
            }                                                                                                          
                                                                                                                       
            if (property_exists($body, 'email') && $body->email != '') {                                               
                $user->set('email', $body->email);                                                                     
            }                                                                                                          
                                                                                                                       
            if (property_exists($body, 'password') && $body->password != '') {                                         
                $user->set('password', crypt($body->password, $app->deps['salt']));                                    
            }                                                                                                          
                                                                                                                       
            if (property_exists($body, 'active' && in_array(strtoupper($body->active, array("Y", "N"))))) {            
                $user->set('active', strtoupper($body->active));                                                       
            }                                                                                                          
                                                                                                                       
            $user->setExpr('date_updated', 'NOW()');                                                                   
                                                                                                                       
            $user->save();                                                                                             
                                                                                                                       
            $app->response->setStatus(204);                                                                            
                                                                                                                       
        } catch (Exception $e) {                                                                                       
                                                                                                                       
            $app->response->setStatus(400);                                                                            
            $app->response->headers->set('Error', $e->getMessage());                                                   
        }                                                                                                              
    });                                                                                                                
                                                                                                                       
    $app->delete('/:id', function($id) use ($app, $auth, $orm) {                                                       
        $auth->check($app, 'Users', 'Delete');                                                                         
                                                                                                                       
        try {                                                                                                          
            $user = $orm::forTable('user')->findOne($id);                                                              
                                                                                                                       
            $user->setExpr('date_archived', 'NOW()');                                                                  
                                                                                                                       
            $user->save();                                                                                             
                                                                                                                       
            $app->response->setStatus(204);                                                                            
                                                                                                                       
        } catch (Exception $e) {                                                                                       
                                                                                                                       
            $app->response->setStatus(400);                                                                            
            $app->response->headers->set('Error', $e->getMessage());                                                   
        }                                                                                                              
    });                                                                                                                
});                                                                                                                    
