<?php

namespace Okami\Core\Routing;

use LogicException;
use Okami\Core\HTTPMethod;
use Okami\Core\Request;
use Okami\Core\Routing\Routes\ControllerRoute;
use Okami\Core\Routing\Routes\FunctionRoute;
use Okami\Core\Routing\Routes\Route;
use Okami\Core\Routing\Routes\TemplateRoute;

/**
 * Class Routable
 *
 * @author Michal Tuček <michaltk1@gmail.com>
 * @package Okami\Core\Routing
 */
abstract class Routable
{
    /**
     * @var RouteCollection
     */
    public RouteCollection $routeCollection;

    /**
     * @var string|null
     */
    protected ?string $pathRoot = null;

    /**
     * Routable constructor.
     */
    public function __construct()
    {
        $this->routeCollection = new RouteCollection();
    }

    /**
     * @param string $path
     *
     * @return RouteGroup
     */
    public function group(string $path): RouteGroup
    {
        $routeGroup = new RouteGroup($this->getPath($path));
        $this->routeCollection->addRouteGroup($routeGroup);

        return $routeGroup;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getPath(string $path): string
    {
        if (!is_null($this->pathRoot)) {
            return $this->pathRoot . $path;
        }

        return $path;
    }

    /**
     * @param array $methods
     * @param string $path
     * @param string|callable|array $callback
     *
     * @return Route
     */
    public function map(array $methods, string $path, $callback): Route
    {
        $route = null;

        $path = $this->getPath($path);

        if (is_string($callback)) {
            /** RENDER TEMPLATE **/
            $route = new TemplateRoute($path, $callback);
        } elseif (is_array($callback)) {
            /** CALL CONTROLLER **/
            $route = new ControllerRoute($path, $callback);
        } elseif (is_callable($callback)) {
            /** EXECUTE FUNCTION **/
            $route = new FunctionRoute($path, $callback);
        }

        if (is_null($route)) {
            throw new LogicException('Requires callback of type string|callable|array but callback with type ' . gettype($callback) . ' passed instead!');
        }

        foreach ($methods as $method) {
            $this->routeCollection->addRoute($route, $method);
        }

        return $route;
    }

    /**
     * @param string $path
     * @param string|callable|array $callback
     *
     * @return Route
     */
    public function get(string $path, $callback): Route
    {
        return $this->map([HTTPMethod::GET], $path, $callback);
    }

    /**
     * @param string $path
     * @param string|callable|array $callback
     *
     * @return Route
     */
    public function post(string $path, $callback): Route
    {
        return $this->map([HTTPMethod::POST], $path, $callback);
    }

    /**
     * @param string $path
     * @param string|callable|array $callback
     *
     * @return Route
     */
    public function put(string $path, $callback): Route
    {
        return $this->map([HTTPMethod::PUT], $path, $callback);
    }

    /**
     * @param string $path
     * @param string|callable|array $callback
     *
     * @return Route
     */
    public function delete(string $path, $callback): Route
    {
        return $this->map([HTTPMethod::DELETE], $path, $callback);
    }

    /**
     * @param string $path
     * @param string|callable|array $callback
     *
     * @return Route
     */
    public function options(string $path, $callback): Route
    {
        return $this->map([HTTPMethod::OPTIONS], $path, $callback);
    }

    /**
     * @param string $path
     * @param string|callable|array $callback
     *
     * @return Route
     */
    public function patch(string $path, $callback): Route
    {
        return $this->map([HTTPMethod::PATCH], $path, $callback);
    }

    /**
     * @param string $path
     * @param string|callable|array $callback
     *
     * @return Route
     */
    public function any(string $path, $callback): Route
    {
        return $this->map([
            HTTPMethod::GET,
            HTTPMethod::POST,
            HTTPMethod::PUT,
            HTTPMethod::DELETE,
            HTTPMethod::OPTIONS,
            HTTPMethod::PATCH
        ], $path, $callback);
    }

    /**
     * @param string $method
     *
     * @return Route[]
     */
    public function getRoutes(string $method): array
    {
        return $this->routeCollection->getRoutesForMethod($method);
    }
}