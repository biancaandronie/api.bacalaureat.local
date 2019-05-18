<?php
// Routes

$app->group('/api', function () use ($app) {
 
    // Version group
    $app->group('/v1', function () use ($app) {
		$app->get('/videos', 'getVideos');
        $app->post('/video', 'getVideo');
        $app->post('/videolink', 'getVideoLink');
        $app->post('/upload', 'addVideo');
		$app->put('/update/{id}', 'updateVideo');
		$app->delete('/delete/{id}', 'deleteVideo');
        $app->get('/comments', 'getComments');
        $app->post('/comment', 'addComment');
	});
});
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});