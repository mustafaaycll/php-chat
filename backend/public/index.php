<?php
/**
 * Entry point of the app.
 * - Boots Slim app.
 * - Registers middleware.
 * - Registers routes.
 * - Runs the app.
 */

use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

// Create PHP-DI container for dependency injection
$container = new Container();

// Load dependency definitions (services, repositories, controllers)
(require __DIR__ . '/../src/dependencies.php')($container);

// Tell Slim to use this container
AppFactory::setContainer($container);

// Create Slim app instance
$app = AppFactory::create();

// Middleware: parse JSON request bodies automatically
$app->add(function ($request, $handler) {
    $contentType = $request->getHeaderLine('Content-Type');

    if (strstr($contentType, 'application/json')) {
        $contents = (string) $request->getBody();
        $parsed = json_decode($contents, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $request = $request->withParsedBody($parsed);
        }
    }

    return $handler->handle($request);
});

// Middleware: error handler
$app->addErrorMiddleware(true, true, true)
    ->setDefaultErrorHandler(function (
        Psr\Http\Message\ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use ($app) {
        $payload = ['error' => $exception->getMessage()];

        $response = $app->getResponseFactory()->createResponse();
        $response->getBody()->write(json_encode($payload));

        // Return 400 for InvalidArgumentException, otherwise 500
        $statusCode = $exception instanceof InvalidArgumentException ? 400 : 500;

        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json');
    }
);

// Load application routes
(require __DIR__ . '/../src/Routes/web.php')($app);

// Run Slim app
$app->run();