<?php
if (isset($argv[2]) && is_dir($argv[2])){
    chdir($argv[2]);
}


$parent_dir = getcwd();

$files_structured = [];
$details_active = false;
$details = [
    "assigment_name" => "",
    "assigment_id" => "",
    "section" => "",
];
echo "Quick grade mode started. Will run without classifications.\n";
if (file_exists('progress.json')){
    echo "Previous GradeBook found. Restoring...\n";
    $files_structured = json_decode(file_get_contents('progress.json'), true);
}
if (file_exists('assigment.json')){
    echo "Previous assigment details found. Restoring...\n";
    $details = json_decode(file_get_contents('assigment.json'), true);
    $details_active = true;
}
$dir_contents = array_diff(scandir(getcwd()), array('.', '..'));
$workbench_dir = "";
foreach ($dir_contents as $content){
    $useable = is_usable($content);
    if ($useable) {
        $files_structured[$useable['file_name']]['student_name'] = $useable['student_name'];
        $files_structured[$useable['file_name']]['student_id'] = $useable['student_id'];
        $files_structured[$useable['file_name']]['file_name'] = $useable['file_name'];
        $files_structured[$useable['file_name']]['late'] = $useable['late'];
    }
}



echo "" . count($dir_contents) . " files scanned, " . count($files_structured) . " usable. \n";

echo "The following submissions were found: \n";

$count = 0;
foreach ($files_structured as $key => $file){
    echo $count++ + 1 . ". " . $file['student_name'] . " - " . $file['student_id'] . " (" . $file['file_name'] . ")\n";
}


if (CLIInputManagerObject::promptYN("Begin screening? (y/n): ")){
    $flatten = CLIInputManagerObject::promptYN("Flatten directories? (y/n): ");
    $auto_make = CLIInputManagerObject::promptYN("Auto make directories? (y/n): ");
}else{
    echo "Stopping service.\n";
    exit();
}
$workbench_name = 'workbench';
$workbench_dir = make_workbench($parent_dir);

label_submission_list:
if ($details_active){
    echo "\nAssigment: \033[35m ".$details['assigment_name']." - ".$details['assigment_id']." (".$details['section'].")\033[0m\n";
}else{
    echo "\nAssigment: \033[35m Unnamed Assigment \033[0m\n";
}
echo "Select submission to grade:\n";
$count = 0;
foreach($files_structured as $key => $file){
    if (!isset($file['late'])) $file['late'] = false;
    echo "----------------------------------------------------------------------------------------------------------------------\n";
    echo $count + 1 . ". \033[31m" . $file['student_name'] . " \033[0m - \033[36m[GRADE: ".(
        (isset($file['grade']) ? ($file['grade'] .' @ '. (isset($file['grade_time']) ? date('Y-m-d H:i:s', $file['grade_time']) : 'N/A')) : "\033[33mN/A\033[0m")
        )."\033[36m]\033[0m".($file['late'] === true ? '[LATE]' : "")." | (ID: " . $file['student_id'] . ") (" . $file['file_name'] . " @ \033[96m".date('m/d/y H:i:s', filemtime($file['file_name']))." \033[0m)\n";
    $count++;
}

if (!$details_active) echo "\nexport. \e[90m[Export Grades] - \e[5m Must run assigment details first \e[25m\e[0m";
else echo "\nexport. \033[35m[Export Grades] \033[0m";
echo "\ndetails. \033[35m[Change Assigment Details] \033[0m";
echo "\n0 or quit. \033[31m[Save and Quit] \033[0m";
echo "\nSelect a submission number or command above:";

file_put_contents('progress.json', json_encode($files_structured));

$option = CLIInputManagerObject::getInputLine();
if ($option != 'quit' && $option != 'export' && $option != 'details' && (!is_numeric($option) || $option < 0 || $option > sizeof($files_structured))){
    show_error("INVALID INPUT Please select a number in range. (Press enter to continue)");
    CLIInputManagerObject::getInputLine();
    goto label_submission_list;
}elseif($option === 'details'){
    echo "\nSection Number: ";
    $details['section'] = CLIInputManagerObject::getInputLine();
    echo "\nAssigment Name: ";
    $details['assigment_name'] = CLIInputManagerObject::getInputLine();
    echo "\nAssigment ID: ";
    $details['assigment_id'] = CLIInputManagerObject::getInputLine();
    echo "\n\033[32mDetails updated. \033[0m\n";
    $details_active = true;
    file_put_contents('assigment.json', json_encode($details));
    goto label_submission_list;

}elseif($option === 'export'){
    if (!$details_active){
        show_error("Error. Project details must be entered first. (Press enter to continue)");
        CLIInputManagerObject::getInputLine();
        goto label_submission_list;
    }
    $assigment_id = $details['assigment_id'];
    $assigment_name = $details['assigment_name'];
    $section = $details['section'];
    echo "\nSave file name (press enter for default): ";
    $name = CLIInputManagerObject::getInputLine();
    if ($name == ''){
        $name = "grades_$assigment_name-$assigment_id@" . date('Y-m-d H:i:s');
    }
    $name .= ".csv";

    echo "Exporting...\n";
    $CSV = "Student,ID,Section, $assigment_name ($assigment_id)\n";
    foreach ($files_structured as $submission){
        $CSV .= "" . $submission['student_name'] . "," . $submission['student_id'] . ',' . $section . "," . ( $submission['grade'] ?? "N\A" ) . "\n";
    }

    file_put_contents($name, $CSV);

    $name = getcwd() . "/" . $name;

    echo "\n\033[32mGradebook export finished successfully. Saved at location: '$name' \033[0m\n";
    echo "Press enter to continue...\n";
    CLIInputManagerObject::getInputLine();
    goto label_submission_list;
}elseif($option === 'quit' || $option == 0){
    echo "Saving...\n";
    file_put_contents('progress.json', json_encode($files_structured));
    echo "Good Bye!\n";
}else{
    file_put_contents('progress.json', json_encode($files_structured));
    $selected = get_from_list($files_structured, $option);
    label_submission_option:
    file_put_contents('progress.json', json_encode($files_structured));
    echo "Selected $option: " . show_file($selected);
    echo "\n1. \033[35m[Open Submission in Grader] \033[0m";
    echo "\n2. \033[35m[Change Submission Grade] \033[0m";
    echo "\n3. \033[35m[Move to Workbench] \033[0m";
    echo "\n4. \033[31m[Delete submission from save] \033[0m";

    echo "\n\n0. \033[33m[<- Back] \033[0m";
    echo "\nSelect an option 0-3: ";
    $option_list = CLIInputManagerObject::getInputLine();

    if (!is_numeric($option_list) || $option_list < 0 || $option_list > 4 ){
        show_error("INVALID INPUT Please select a number in range.");
        goto label_submission_option;
    }
    switch($option_list){
        case 0:
            goto label_submission_list;
            break;
        case 1:
            echo "Moving into workbench...\n";
            $workbench_dir = make_workbench($parent_dir);
            set_up($selected['file_name'], $workbench_dir, $parent_dir, $flatten, $auto_make);
            chdir($workbench_dir);
            echo "You are now in a partitioned environment with only this submission.\n";
            echo "You can run any commands that you would run in the terminal (like 'ls -l') by just typing them. \nTo read the README file, type 'readme'. \nTo change the grade for this assignment, type 'grade'. \nTo exit this environment, type 'exit', '0' or 'quit'.\nTo grade and exit, type 'gq'.\n";
            $hold = true;
            while($hold){
                $command = CLIInputManagerObject::getInputLine();
                $quit_when_done = false;
                switch ($command){
                    case "exit":
                    case "quit":
                    case "0":
                        $hold = false;
                    break;
                    case "readme":
                    case "README":
                        readme();
                    break;
                    case "gq":
                        $quit_when_done = true;
                    case "grade":
                        echo "Change grade for this submission to: ";
                        $grade = CLIInputManagerObject::getInputLine();
                        if (!is_numeric($grade) ){
                            show_error("INVALID INPUT Please select a number in range. (Press enter to continue)");
                            CLIInputManagerObject::getInputLine();
                            goto label_submission_changegrade_option;
                        }
                        if(change_grade($files_structured[$selected['file_name']], $grade)){
                            echo "\n\033[32mGrade changed successfully.\033[0m\n";
                        }else{
                            echo "\nGrade could not be changed.\n";
                        }
                        if ($quit_when_done){
                            $hold = false;
                            goto label_submission_list;
                        }
                        break;
                    default:
                        $output = [];
                        exec($command, $output, $return_var);
                        foreach ($output as $out){
                            echo "$out\n";
                        }
                        break;

                }
            }
            chdir($parent_dir);
            goto label_submission_option;
            break;
        case 2:
            label_submission_changegrade_option:
            echo "Change grade for this submission to: ";
            $grade = CLIInputManagerObject::getInputLine();
            if (!is_numeric($grade) ){
                show_error("INVALID INPUT Please select a number in range. (Press enter to continue)");
                CLIInputManagerObject::getInputLine();
                goto label_submission_changegrade_option;
            }
            if(change_grade($files_structured[$selected['file_name']], $grade)){
                echo "\n\033[32mGrade changed successfully.\033[0m\n";
            }else{
                echo "\nGrade could not be changed.\n";
            }
            goto label_submission_option;
            break;
        case 3:
            echo "Moving submission to a new workbench...\n";
            $workbench_dir = make_workbench($parent_dir);
            set_up($selected['file_name'], $workbench_dir, $parent_dir, $flatten, $auto_make);
            goto label_submission_option;
            break;
        case 4:
            if(CLIInputManagerObject::promptYN("Are you sure you want to delete this submission? The actual file will not be deleted, 
            however the grade will be lost. (y/n)")){
                unset($files_structured[$selected['file_name']]);
                goto label_submission_list;
            }else{
                goto label_submission_option;
            }

    }
}



function is_usable($file_name) {
    $file_parts = pathinfo($file_name);

    switch($file_parts['extension'])
    {
        case "zip":
            $parts = explode('_', $file_name);
            if (count($parts) >= 2 && is_numeric($parts[1])){
                return [
                    'student_name' => $parts[0],
                    'student_id' => $parts[1],
                    'file_name' => $file_name,
                    'late' => false

                ];
            }
            if (count($parts) >= 2 && is_numeric($parts[2]) && $parts[1] == 'LATE'){
                return [
                    'student_name' => $parts[0],
                    'student_id' => $parts[2],
                    'file_name' => $file_name,
                    'late' => true
                ];
            }
            break;
    }
    return false;
}

function get_from_list($files_structured, $number){
    $count = 1;

    foreach($files_structured as $file){
        if ($number == $count++){
            return $file;
        }
    }
}

function change_grade(&$files_structured, $grade){
    $files_structured['grade'] = $grade;
    $files_structured['grade_time'] = time();
    return true;
}

function readme(){
    if (!file_exists('README')){
        error_log("Readme file does not exist.");
        return;
    }
    echo "README FILE: \n";
    echo file_get_contents('README');
}

function set_up($file_name, $workbench_dir, $parent_dir, $flatten, $auto_make){
    chdir($workbench_dir);
    shell_exec("unzip ".($flatten ? '-j' : '')." ../$file_name");
    if ($auto_make){
        echo "Making file (will sleep SpeedGrader for 3 seconds to give make time to run)...\n";
        shell_exec('make');
        sleep(3.5);
    }
    chdir($parent_dir);
}

function show_error($message){
    echo "\n\033[31m$message\033[0m\n";
}

function show_file($file){
    return "\033[31m" . $file['student_name'] . " \033[0m - \033[36m[GRADE: ".(
    (isset($file['grade']) ? ($file['grade'] .' @ '. (isset($file['grade_time']) ? date('Y-m-d H:i:s', $file['grade_time']) : 'N/A')) : "\033[33mN/A\033[0m")
    )."\033[36m]\033[0m".($file['late'] === true ? '[LATE]' : "")." | (ID: " . $file['student_id'] . ") (" . $file['file_name'] . " @ \033[96m".date('m/d/Y H:i:s', filemtime($file['file_name'])).""."\033[0m)";
}

function make_workbench($parent_dir){
    $workbench_name = 'workbench';
    if (is_dir(($workbench_name))){
        shell_exec("rm -rf $workbench_name");
    }
    if (!mkdir($concurrentDirectory =  $workbench_name) && !is_dir($concurrentDirectory)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
    return $parent_dir . "/" . $workbench_name;
}