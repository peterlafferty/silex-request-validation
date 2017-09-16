<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Silex\Provider\ValidatorServiceProvider;

$app = new Silex\Application();
unset($app['exception_handler']);
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

$app->register(new ValidatorServiceProvider());

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

$app->get('/emoji/', function (Request $request) use ($app) {

    $constraints = new Assert\Collection([
        'hasSkinTone' => [
            new Assert\NotBlank(),
            new Assert\Regex("/true|false/")
        ],
        'idBelow' => [
            new Assert\NotBlank(),
            new Assert\Regex([
                "pattern" => '/\d+/',
                'message' => "value should be positive int"
            ]),
            new Assert\GreaterThan(0)
        ]
    ]);

    $getQuery = $request->query->all();
    $errors =  $app['validator']->validate(
        $getQuery,
        $constraints
    );

    if (count($errors) > 0) {
        $messages = [];
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' ' . $error->getMessage();
        }
        return new Utf8JsonResponse($messages, 400);
    }

    //at this point we know the values are present and can convert them
    $hasSkinTone  = filter_var(
        $getQuery['hasSkinTone'],
        FILTER_VALIDATE_BOOLEAN
    );
    $idBelow = (int)$getQuery['idBelow'];

    $filterEmoji = [];
    foreach ($app['emoji'] as $emoji) {
        if ($emoji->id < $idBelow && $emoji->hasSkinTone == $hasSkinTone) {
            $filterEmoji[] = $emoji;
        }
    }

    return new Utf8JsonResponse(["emoji" => $filterEmoji]);
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
})->assert("id", "\d+");

$app->post('/emoji/', function (Request $request) use ($app) {
    return new Utf8JsonResponse(null, 204);
});


$app->run();
