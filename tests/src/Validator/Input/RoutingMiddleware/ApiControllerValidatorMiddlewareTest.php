<?php namespace FHTeam\LaravelValidator\Tests\Validator\Input\RoutingMiddleware;

use FHTeam\LaravelValidator\Tests\Fixture\Input\ApiControllerValidatorMiddlewareFixture;
use FHTeam\LaravelValidator\Tests\Validator\Input\InputValidatorTestBase;
use FHTeam\LaravelValidator\Validator\Input\RoutingMiddleware\ApiControllerValidatorMiddleware;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;

/**
 * Class FrontendControllerValidatorMiddlewareTest
 *
 * @package FHTeam\LaravelValidator\Test\Input\RoutingMiddleware
 */
class ApiControllerValidatorMiddlewareTest extends InputValidatorTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setCurrentGroup('group');
    }

    public function testInstanceOf()
    {
        $this->setRequestData(['int' => 5]);
        $instance = $this->createControllerValidator();
        $this->assertInstanceOf(Middleware::class, $instance);
        $this->assertInstanceOf(ApiControllerValidatorMiddleware::class, $instance);
    }

    public function testValidateOk()
    {
        $this->setRequestData(['int' => 5]);
        $instance = $this->createControllerValidator();
        $this->assertNull(
            $instance->handle(
                $this->request,
                function ($value) {
                    return $value;
                }
            )
        );
    }

    public function testValidateRedirect()
    {
        $this->setRequestData(['int' => -1]);
        $instance = $this->createControllerValidator();

        /** @var JsonResponse $result */
        $result = $instance->handle(
            $this->request,
            function ($value) {
                return $value;
            }
        );

        $this->assertInstanceOf(JsonResponse::class, $result);

        $expected = '{"validationErrors":{"int":{"Min":["1"]}}}';
        $this->assertEquals($expected, $result->getContent());
    }

    /**
     * @return Middleware
     */
    protected function createControllerValidator()
    {
        return $this->app->make(
            ApiControllerValidatorMiddlewareFixture::class,
            [
                $this->validatorFactory,
                $this->request,
                $this->router,
                $this->app->make(ResponseFactory::class)
            ]
        );
    }
}
