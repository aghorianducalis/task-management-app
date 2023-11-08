<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class JsonMiddleware
{
    /**
     * The Response Factory our app uses.
     *
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected ResponseFactory $factory;

    /**
     * JsonMiddleware constructor.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory $factory
     */
    public function __construct(ResponseFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        if (! $response instanceof JsonResponse) {
            $response = empty($response->content()) ?
                $this->factory->noContent(
                    $response->status(),
                    $response->headers->all()
                ) :
                $this->factory->json(
                    $response->content(),
                    $response->status(),
                    $response->headers->all()
                );
        }

        return $response;
    }
}
