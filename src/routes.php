<?php
// Routes

$app->group('/api', function () use ($app) {
 
    // Version group
    $app->group('/v1', function () use ($app) {
		$app->get('/videos', 'getVideos');
		$app->get('/video/{id}', 'getVideo');
		$app->post('/create', 'addVideo');
		$app->post('/upload', 'addVideoFile');
		$app->put('/update/{id}', 'updateVideo');
		$app->delete('/delete/{id}', 'deleteVideo');
	});
});
