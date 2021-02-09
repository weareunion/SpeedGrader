<?php


class ClassManagerObject
{
    public static $classes = [];
    public static $classes_buckets = [];
    static function refresh(){
        if (!file_exists(SpeedGraderObject::getSupportingDirectory() . '/ClassManagerState.json')){
            file_put_contents(SpeedGraderObject::getSupportingDirectory() . '/ClassManagerState.json', "{}");
        }
        self::$classes_buckets = json_decode(file_get_contents(SpeedGraderObject::getSupportingDirectory() . '/ClassManagerState.json'), true);
    }

    /**
     * @return array
     */
    public static function getClasses(): array
    {
        return self::$classes;
    }

    /**
     * @return array
     */
    public static function getClassesBuckets(): array
    {
        return self::$classes_buckets;
    }


    public static function addClass(ClassObject $class): void{
        if (!is_dir(SpeedGraderObject::getSupportingDirectory() . '/classes/')) {
            if (!mkdir($concurrentDirectory = SpeedGraderObject::getSupportingDirectory() . '/classes/') && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        $file_to_write = SpeedGraderObject::getSupportingDirectory() . '/classes/' . $class->getBucketId() . '.json';
        if (!file_exists($file_to_write)){
            file_put_contents($file_to_write, "{}");
        }
        self::$classes[] = $class;
        self::$classes_buckets[] = $class->getBucketId();
        self::save();
    }

    static function save(){
        file_put_contents(SpeedGraderObject::getSupportingDirectory() . '/ClassManagerState.json', json_encode(self::$classes_buckets, JSON_THROW_ON_ERROR));
    }

    static function toString()
    {
        $string = "";
        if (sizeof(self::$classes_buckets) === 0){
            return "No classes found!\n";
        }
        foreach (self::$classes_buckets as $key => $class){
            $string .= $key+1 . ": $class\n";
        }
        return $string;
    }

}