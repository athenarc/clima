<?php

namespace app\components;

class ResourceContext
{
    const MEMORY=0;
    const CPU=1;
    const IP=2;

    public static function getContextManager($context=null) {
        $context ??= -1;
        $neutralContextManager = function ($input) { return $input; };
        $qualifiedContextManager = null;
        switch ($context) {
            case self::MEMORY:
                $qualifiedContextManager=function($gigaBytes)
                    {
                        $teraBytes = round($gigaBytes / 1024, 1);
                        if ($teraBytes > 1) return $teraBytes . ' TBs';
                        else if ($teraBytes == 1) return $teraBytes . ' TB';
                        else if ($gigaBytes != 1) return $gigaBytes . ' GBs';
                        return $gigaBytes . ' GB';
                    };
                break;
            case self::IP:
                $qualifiedContextManager=function($numIps)
                    {
                        return $numIps . ' IP' . ($numIps > 1 ? 's' : '');
                    };
                break;
            case self::CPU:
                $qualifiedContextManager=function($numCpus)
                    {
                        return $numCpus . ' core' . ($numCpus > 1 ? 's' : '');
                    };
                break;
            default:
                $qualifiedContextManager = $neutralContextManager;
                break;
        }
        return $qualifiedContextManager;
    }
}