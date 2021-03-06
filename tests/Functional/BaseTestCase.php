<?php
namespace Tests\Functional;

use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;

class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     * @return ResponseInterface
     */
    public function runApp($requestMethod, $requestUri, $requestData = null)
    {
        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);
        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }
        // Set up a response object
        $response = new Response();
        // Use the application settings
        $settings = require __DIR__ . '/../../src/settings.php';
        // Instantiate the application
        $app = new App($settings);
        // Set up dependencies
        require __DIR__ . '/../../src/dependencies.php';

        require __DIR__ . '/../../src/routes/getOptimalCyclingRoute.php';
        require __DIR__ . '/../../src/routes/getOptimalDrivingRoute.php';
        require __DIR__ . '/../../src/routes/getOptimalDrivingTrafficRoute.php';
        require __DIR__ . '/../../src/routes/getOptimalWalkingRoute.php';
        // Process the application
        $resp = $app->process($request, $response);
        // Return the response
        return $resp;
    }
}