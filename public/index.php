<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;

require __DIR__ . '/../vendor/autoload.php';

session_start();
if(empty($_SESSION['cart'])){
    $_SESSION['cart'] = new \Source\Cart;
}

$app = AppFactory::create();


// Define Custom Error Handler
$customErrorHandler = function (
    Psr\Http\Message\ServerRequestInterface $request,
    \Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    // seems the followin can be replaced by your custom response
    // $page = new Alvaro\Pages\Error($c);
    // return $page->notFound404($request, $response);
    $response->getBody()->write('not found');
    return $response->withStatus(404);
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
// Register the handler to handle only  HttpNotFoundException
// Changing the first parameter registers the error handler for other types of exceptions
$errorMiddleware->setErrorHandler(Slim\Exception\HttpNotFoundException::class, $customErrorHandler);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/', function (Request $request, Response $response, $args) {
    session_destroy();
    $response->getBody()->write("Hello world!");

    return $response;
});

$app->get('/test', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Testing!");

    return $response;
});

$app->get('/showCart', function (Request $request, Response $response, $args) {
    $response->getBody()->write($_SESSION['cart']->showList());

    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->post('/add', function (Request $request, Response $response, $args) {
    $params = (array)$request->getParsedBody();
    $_SESSION['cart']->addProduct(new \Source\Product($params['id'],$params['name'],$params['price'],$params['quantity'])); 
    $response->getBody()->write(json_encode($params));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->get('/remove/{key}/{keyword}', function (Request $request, Response $response, $args) {
    try{
        $removed = $_SESSION['cart']->removeProduct($args['key'], $args['keyword']);
    }catch(Throwable $t){
        $removed= -1;
        $response->getBody()->write(json_encode(['status' => false, 'elements' => 0]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
        $response->getBody()->write(json_encode(['status' => true, 'elements' => $removed]));
    
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/edit/{originalKey}/{originalValue}/{key}/{value}', function (Request $request, Response $response, $args) {
    try{
        $_SESSION['cart']->editProduct($args['originalKey'], $args['originalValue'],$args['key'],$args['value']);
    }catch(Throwable $t){
        if($t){    
            $response->getBody()->write(json_encode(['status' => false, 'error' => $t->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
        $response->getBody()->write(json_encode(['status' => true]));
    
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

$app->run();