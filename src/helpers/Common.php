<?php

namespace craftsnippets\dpdeasyship\helpers;
use Craft;

class Common
{
    public static function addLog($txt, $fileName = 'dpd-easyship'){
        $file = Craft::getAlias('@storage/logs/'.$fileName.'.log');
        if(is_array($txt) || is_object($txt)){
            $txt = json_encode($txt);
        }
        $log = date('Y-m-d H:i:s').' '.$txt."\n";
        \craft\helpers\FileHelper::writeToFile($file, $log, ['append' => true]);
    }
}