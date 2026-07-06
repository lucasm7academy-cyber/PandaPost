<?php
$user = App\Models\User::find('019e50a7-9fc8-7302-be0c-348b23f53b42');
Auth::login($user);
$c = app(App\Http\Controllers\App\PostController::class);
$r = new Illuminate\Http\Request();
$r->setUserResolver(function() use ($user) { return $user; });
app()->instance('request', $r);
$resp = $c->create($r)->toResponse($r)->getOriginalContent();
echo json_encode($resp->getData()['page']['props']['signatures']);
