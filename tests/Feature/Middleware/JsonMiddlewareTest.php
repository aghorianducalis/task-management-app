<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\JsonMiddleware;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Middleware\JsonMiddleware
 */
class JsonMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @covers ::handle
     */
    public function test_it_should_set_to_application_json()
    {
        $mockRequest = $this->getRequest();
        /** @var JsonMiddleware $middleware */
        $middleware = app(JsonMiddleware::class);

        $response = $middleware->handle(
            $mockRequest,
            function (Request $request) {
                $this->assertSame('application/json', $request->headers->get('Accept'));

                return response($request->getContent());
            }
        );

        $this->assertInstanceOf(Response::class, $response);
    }

    private function getRequest($headers = []): Request
    {
        $request = new Request([], [], [], [], [], [], '');

        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $request;
    }
}
