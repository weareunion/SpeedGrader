<?php


class SpeedGraderObject
{
    static public $config = [
        "grader" => [
            "name" => ""
        ],
        "institution" => "Clemson University",
        "directories" => [
            'supporting' => '~/SpeedGrader/supporting',
        ]
    ];
    static function loadConfig(){
        self::$config = json_decode(file_get_contents('~/SpeedGrader/config.json'), true, 512, JSON_THROW_ON_ERROR);
    }
    static function saveConfig(){
        if(!(is_dir('~/SpeedGrader/speedgrader'))){
            if (!mkdir('~/SpeedGrader/speedgrader') && !is_dir('~/SpeedGrader/speedgrader')) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created. This program will fail.', '~/SpeedGrader/speedgrader'));
            }
        }
        file_put_contents('~/SpeedGrader/config.json', json_encode(self::$config, JSON_THROW_ON_ERROR));
    }
    static function
    setSupportingDirectory($dir=""){
        if ($dir === ""){
            $dir = self::$config['directories']['supporting'];
        }
        if (!is_dir($dir)){
            if (!mkdir($dir) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created. Please manually create this directory and try again.', $dir));
            }
        }
        $dir .= '/workbench';
        if (!is_dir($dir)){
            if (!mkdir($dir) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created. Please manually create this directory and try again.', $dir));
            }
        }
        self::$config['directories']['supporting'] = $dir;
        self::saveConfig();
    }
    static function getSupportingDirectory(){
        return self::$config['directories']['supporting'];
    }
    static function setName($name){
        self::$config['grader']['name'] = $name;
        self::saveConfig();
    }
    static function getName(){
       return self::$config['grader']['name'];
    }
}