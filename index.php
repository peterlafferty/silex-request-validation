<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();

$app['emoji'] = json_decode('[
    {
        "id": 1,
        "code": "U+1F600",
        "value": "😀",
        "description": "grinning face",
        "hasSkinTone": false
    },
    {
        "id": 2,
        "code": "U+1F601",
        "value": "😁",
        "description": "grinning face with smiling eyes",
        "hasSkinTone": false
    },
    {
        "id": 99,
        "code": "U+1F466",
        "value": "👦",
        "description": "boy",
        "hasSkinTone": true
    },
    {
        "id": 105,
        "code": "U+1F467",
        "value": "👧",
        "description": "girl",
        "hasSkinTone": true
    }
]');

/* ignore this hackiness, it makes it simpler to set the headers and escape options*/
class Utf8JsonResponse extends JsonResponse
{
    public function __construct($data = null, $status = 200, $headers = array(), $json = false)
    {
        $headers = array_merge(
            ["Content-Type" => "application/json; charset=utf-8"],
            $headers
        );
        $this->encodingOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        parent::__construct($data, $status, $headers, $json);
    }
}

$app->get('/emoji/', function () use ($app) {
    return new Utf8JsonResponse(["emoji" => $app['emoji']]);
});

$app->get('/emoji/{id}', function (int $id) use ($app) {
    $filtered = array_filter($app['emoji'], function ($k) use ($id) {
        return $k->id == $id;
    });
    if (empty($filtered)) {
        return new Utf8JsonResponse(null, 404);
    }

    $response = new Utf8JsonResponse(array_pop($filtered));

    return $response;
});

$app->post('/emoji/', function (Request $request) use ($app) {
    return new Utf8JsonResponse(null, 204);
});


$app->run();
