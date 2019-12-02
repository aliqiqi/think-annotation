<?php
declare (strict_types = 1);

namespace think\annotation\handler;

use Doctrine\Common\Annotations\Annotation;
use think\annotation\handler\Handler;

final class Doc extends Handler
{
    public function func(\ReflectionMethod $refMethod, Annotation $annotation, \think\route\RuleItem &$rule)
    {
        new \think\annotation\document\Doc($annotation,$rule);
    }
}
