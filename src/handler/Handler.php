<?php
/** Created by 嗝嗝<china_wangyu@aliyun.com>. Date: 2019-11-14  */

namespace think\annotation\handler;


use Doctrine\Common\Annotations\Annotation;

abstract class Handler implements HandleInterface
{
    public function cls(\ReflectionClass $refClass, Annotation $annotation, \think\Route &$route)
    {
        // TODO: Implement cls() method.
    }

    public function func(\ReflectionMethod $refMethod, Annotation $annotation, \think\route\RuleItem &$rule)
    {
        // TODO: Implement func() method.
    }

    public function isCurrentMethod(\ReflectionMethod $refMethod,\think\route\RuleItem $rule){
        if (PHP_SAPI != 'cli'){
            if(
                $refMethod->class.'@'.$refMethod->name == $rule->checkUrl(request()->url())->getRoute()
                and
                $rule->getMethod() == strtolower(request()->method())
            ){
                return true;
            }
        }
        return false;
    }
}