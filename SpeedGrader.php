<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once __DIR__ . '/src/CLIInputManagerObject.php';
require_once __DIR__ . '/src/SpeedGraderObject.php';
require_once __DIR__ . '/src/ClassManagerObject.php';
require_once __DIR__ . '/src/ClassObject.php';
require_once __DIR__ . '/src/ProjectObject.php';

echo "SpeedGrader V.1.0 - Designed for use with Canvas\n";
echo "Karl Schmidt (2021) @ Class of 2022\n";

if (!file_exists('speedgrader/config.json')){
    require_once __DIR__ . '/src/scripts/SetupWizard.php';
}

if ($argv['1'] === 'quick') {
    require_once __DIR__ . '/src/scripts/ControlFlowQuick.php';
}else{
    require_once __DIR__ . '/src/scripts/ControlFlow.php';
}


//echo "What do you want to do? Please enter the index of your choice.\n";
//echo "1. Create new project\n";
//echo "2. Open existing project\n";
//echo "3. Edit Students\n";
//echo "4. Switch Class\n";
//echo (CLIInputManagerObject::promptYN() ? "Yes" : "No");
//echo "Please select from the given list of projects:\n";