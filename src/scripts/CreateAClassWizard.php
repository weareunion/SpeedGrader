<?php
function createAClassWizard(){
    echo "[NEW][CLASS][1] What is the name of your class?\n";
    $name = CLIInputManagerObject::getInputLine();
    echo "[NEW][CLASS][2] What is your class ID? (This can be any number, but it would be best if it was the CANVAS course ID.)\n";
    $id = CLIInputManagerObject::getInputLine();

    $class = new ClassObject($name, $id);
    ClassManagerObject::addClass($class);

    echo "[NEW][CLASS][DONE] Class has been added. (Press enter to continue)\n";
    CLIInputManagerObject::getInputLine();
}