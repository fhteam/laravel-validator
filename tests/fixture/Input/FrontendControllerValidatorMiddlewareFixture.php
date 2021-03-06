<?php namespace FHTeam\LaravelValidator\Tests\Fixture\Input;

use FHTeam\LaravelValidator\Validator\Input\RoutingMiddleware\FrontendControllerValidatorMiddleware;

class FrontendControllerValidatorMiddlewareFixture extends FrontendControllerValidatorMiddleware
{
    protected $rules = [
        'group' => [
            'int' => 'required|numeric|min:1|max:10',
        ],
    ];

    protected $errorRedirects = [
        'group' => [
            'route' => 'test_route',
        ]
    ];
}
