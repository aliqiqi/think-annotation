<?php

namespace think\annotation;

use Doctrine\Common\Annotations\Reader;
use think\annotation\route\Group;
use think\annotation\route\Route;
use think\annotation\route\Resource;
use think\annotation\route\Doc;
use think\annotation\route\Param;
use think\annotation\route\Middleware;
use think\annotation\route\Jwt;
use think\annotation\route\Rule;
use think\annotation\route\Validate;
use think\annotation\route\Model;
use think\App;
use think\event\RouteLoaded;
use think\route\RuleGroup;

/**
 * Trait InteractsWithRoute
 * @package think\annotation\traits
 * @property App $app
 * @property Reader $reader
 */
trait InteractsWithRoute
{

    /**
     * @var \think\Route
     */
    protected $route;


    /**
     * 注册注解路由
     */
    protected function registerAnnotationRoute()
    {
        if ($this->app->config->get('annotation.route.enable', true)) {
            $this->app->event->listen(RouteLoaded::class, function () {

                $this->route = $this->app->route;

                $dirs = [$this->app->getAppPath() . $this->app->config->get('route.controller_layer')]
                    + $this->app->config->get('annotation.route.controllers', []);

                foreach ($dirs as $dir) {
                    if (is_dir($dir)) {
                        $this->scanDir($dir);
                    }
                }
            });
        }
    }


    protected function scanDir($dir)
    {
        foreach ($this->findClasses($dir) as $class) {
            $refClass = new \ReflectionClass($class);
            $routeGroup = false;
            $this->setClassAnnotations($refClass,$routeGroup);
            //方法
            foreach ($refClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $refMethod) {
                /** @var Route $route */
                if ($route = $this->reader->getMethodAnnotation($refMethod, Route::class)) {
                    //注册路由
                    $rule = $routeGroup->addRule($route->value, "{$class}@{$refMethod->getName()}", $route->method);
                    $rule->option($route->getOptions());
                    $this->setMethodAnnotations($refMethod,$rule);
                }
            }
        }
    }

    protected function setMethodAnnotations($refMethod,$rule)
    {
        //方法
        $annotations = $this->reader->getMethodAnnotations($refMethod);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Route)continue;
            //中间件
            if ($annotation instanceof Middleware) {
                $rule->middleware($annotation->value);
            }
            //设置分组别名
            if ($annotation instanceof Group) {
                $rule->group($annotation->value);
            }
            //绑定模型,支持多个
            if (!empty($annotation instanceof Model)) {
                /** @var Model $model */
                $rule->model($model->var, $model->value, $model->exception);
            }
            //验证
            /** @var Validate $validate */
            if ($annotation instanceof Validate) {
                $rule->validate($annotation->value, $annotation->scene, $annotation->message, $annotation->batch);
            }
            if (isset($this->annotation[get_class($annotation)])) {
                $class = $this->annotation[get_class($annotation)];
                (new $class())->func($refMethod, $annotation, $rule);
            }
        }
    }

    protected function setClassAnnotations(\ReflectionClass $refClass,&$routeGroup)
    {
        $routeMiddleware = [];
        $callback = null;
        $class = $refClass->getName();
        $annotations = $this->reader->getClassAnnotations($refClass);
        foreach ($annotations as $annotation) {
            //类
            /** @var Resource $resource */
            if ($annotation instanceof Resource) {
                //资源路由
                $callback = function () use ($class, $annotation) {
                    $this->route->resource($annotation->value, $class)
                        ->option($annotation->getOptions());
                };
            }
            if ($annotation instanceof Middleware) {
                $routeGroup = '';
                $routeMiddleware = $annotation->value;
            }
            /** @var Group $group */
            if ($annotation instanceof Group) {
                $routeGroup = $annotation->value;
                if (false !== $routeGroup) {
                    $routeGroup = $this->route->group($routeGroup, $callback);
                    if ($annotation) {
                        $routeGroup->option($annotation->getOptions());
                    }
                    $routeGroup->middleware($routeMiddleware);
                } else {
                    if ($callback) {
                        $callback();
                    }
                    $routeGroup = $this->route->getGroup();
                }
            }
            /** @var  */
            if (isset($this->annotation[get_class($annotation)])) {
                $class = $this->annotation[get_class($annotation)];
                (new $class())->cls($refClass, $annotation, $this->route);
            }
        }
    }
}
