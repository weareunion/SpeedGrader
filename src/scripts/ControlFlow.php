<?php

label_start_flow:
echo "\nChoose a class to get started:\n";

ClassManagerObject::refresh();
echo ClassManagerObject::toString();

echo "\n0. [Create a new class]\nSelect option #:";

$option = CLIInputManagerObject::getInputLine();

if ($option === 0){
    require_once __DIR__ . '/CreateAClassWizard.php';
    CreateAClassWizard();
    goto label_start_flow;
}

if (!is_numeric($option) || $option < 0 || $option > sizeof(ClassManagerObject::getClassesBuckets()) ){
    echo "\nINVALID! Please select a number in range.\n";
    goto label_start_flow;
}