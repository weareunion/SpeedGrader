<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once __DIR__ . '/src/CLIInputManagerObject.php';
require_once __DIR__ . '/src/SpeedGraderObject.php';
require_once __DIR__ . '/src/ClassManagerObject.php';
require_once __DIR__ . '/src/ClassObject.php';
require_once __DIR__ . '/src/ProjectObject.php';
echo "  _____                         _   _____                  _              __      __ ___    __ 
 / ____|                       | | / ____|                | |             \ \    / // _ \  /_ |
| (___   _ __    ___   ___   __| || |  __  _ __  __ _   __| |  ___  _ __   \ \  / /| | | |  | |
 \___ \ | '_ \  / _ \ / _ \ / _` || | |_ || '__|/ _` | / _` | / _ \| '__|   \ \/ / | | | |  | |
 ____) || |_) ||  __/|  __/| (_| || |__| || |  | (_| || (_| ||  __/| |       \  /  | |_| |_ | |
|_____/ | .__/  \___| \___| \__,_| \_____||_|   \__,_| \__,_| \___||_|        \/    \___/(_)|_|
        | |                                                                                    
        |_|                                                                                    
";
echo "SpeedGrader V.1.0 - Designed for use with Canvas\n";
echo "Karl Schmidt (2021) @ Class of 2022\n\n";

if (!file_exists('speedgrader/config.json')){
    require_once __DIR__ . '/src/scripts/SetupWizard.php';
}

if ($argv['1'] === 'quick') {
    require_once __DIR__ . '/src/scripts/ControlFlowQuick.php';
}else{
    echo "You're probably looking for quick grading mode. What you are accessing is an unfinished feature.\n";
    echo "Rerun your command as 'php <SpeedGrader.php file> quick [directory to grade]'\n";
    echo "If this was intentional, press enter\n";
    CLIInputManagerObject::getInputLine();
    require_once __DIR__ . '/src/scripts/ControlFlow.php';
}


//echo "What do you want to do? Please enter the index of your choice.\n";
//echo "1. Create new project\n";
//echo "2. Open existing project\n";
//echo "3. Edit Students\n";
//echo "4. Switch Class\n";
//echo (CLIInputManagerObject::promptYN() ? "Yes" : "No");
//echo "Please select from the given list of projects:\n";