<?php


class ClassObject
{
    public $name;
    public $class_id;
    public $students;
    public $projects;

    /**
     * ClassObject constructor.
     * @param $name
     * @param $class_id
     */
    public function __construct( $name, $class_id )
    {
        $this->name = $name;
        $this->class_id = $class_id;
    }

    public function getBucketId(){
        return $this->name . '-' . $this->class_id;
    }


}