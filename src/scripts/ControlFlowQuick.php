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
if (file_exists("$parent_dir/progress.json")){
    echo "[!] Previous GradeBook found. Restoring...\n";
    $files_structured = json_decode(file_get_contents("$parent_dir/progress.json"), true);
}
if (file_exists("$parent_dir/assigment.json")){
    echo "[!] Previous assigment details found. Restoring...\n";
    $details = json_decode(file_get_contents("$parent_dir/assigment.json"), true);
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



echo "\n[->] " . count($dir_contents) . " files scanned, " . count($files_structured) . " usable. \n";

if (count($files_structured) === 0){
    show_error("No usable files were found. Unzip the submissions.zip from Canvas and run in the resulting directory.");
    exit(1);
}

echo "\nThe following submissions were found: \n";

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
$show_comments = false;
label_submission_list:
chdir($parent_dir);
system('clear');
if ($details_active){
    echo "\nAssignment: \033[35m ".$details['assigment_name']." - ".$details['assigment_id']." (".$details['section'].")\033[0m\n";
}else{
    echo "\nAssignment: \033[35m Unnamed Assignment \033[0m\n";
}
echo "Select submission to grade:\n";
$count = 0;
foreach($files_structured as $key => $file){
    if (!$show_comments) {
        if (!isset($file['late'])) $file['late'] = false;
        echo "----------------------------------------------------------------------------------------------------------------------\n";
        echo $count + 1 . ". \033[31m" . $file['student_name'] . " \033[0m - \033[36m[" . (
            ( isset($file['grade']) ? ( 'GRADE: ' . $file['grade'] . ' @ ' . ( isset($file['grade_time']) ? date('Y-m-d H:i:s', $file['grade_time']) : 'N/A' ) ) : "\033[34m -Not yet graded- \033[0m" )
            ) . "\033[36m]\033[0m" . ( $file['late'] === true ? '[LATE]' : "" ) . " | (ID: " . $file['student_id'] . ") (" . $file['file_name'] . " @ \033[96m" . date('m/d/y H:i:s', filemtime($file['file_name'])) . " \033[0m)\n";
    }else{
        echo "----------------------------------------------------------------------------------------------------------------------\n";
        echo "" . ($count + 1) . ". " . show_file_comments($file) . "\n Deductions\n" . show_file_deductions($file) . "\n";
    }
    $count++;
}


if ($count == 0){
    echo "\nNo submissions found. \033[0m\n";
}

if (!$details_active) echo "\nexport. \e[90m[Export Grades] - \e[5m Must run assigment details first \e[25m\e[0m";
else echo "\ne. \033[35m[Export Grades] \033[0m";
echo  ($show_comments ? "\nc. \033[35m[Hide Comments] \033[0m" : "\nc. \033[35m[Show Comments] \033[0m");
echo "\nd. \033[35m[Change Assignment Details] \033[0m";
echo "\n0 or quit. \033[31m[Save and Quit] \033[0m";
echo "\nSelect a submission number or command above:";

file_put_contents("$parent_dir/progress.json", json_encode($files_structured));

$option = CLIInputManagerObject::getInputLine();
if ($option != 'quit' && $option != 'e' && $option != 'd' && $option != 'c' && (!is_numeric($option) || $option < 0 || $option > sizeof($files_structured))){
    show_error("INVALID INPUT Please select a number in range. (Press enter to continue)");
    CLIInputManagerObject::getInputLine();
    goto label_submission_list;
}elseif($option === 'd'){
    system('clear');
    echo "\nEnter your section details:";
    echo "\nSection Number: (Must match Canvas)";
    $details['section'] = CLIInputManagerObject::getInputLine();
    echo "\nAssignment Name: (Can be anything)";
    $details['assigment_name'] = CLIInputManagerObject::getInputLine();
    echo "\nAssignment ID: (Must match Canvas - Last part of the canvas URL)";
    $details['assigment_id'] = CLIInputManagerObject::getInputLine();
    echo "\n\033[32mDetails updated. \033[0m\n";
    $details_active = true;
    file_put_contents("$parent_dir/assigment.json", json_encode($details));
    goto label_submission_list;

}elseif($option === 'e'){
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
        if (!isset($submission['grade'])) continue;
        $CSV .= "" . $submission['student_name'] . "," . $submission['student_id'] . ',' . $section . "," . ( $submission['grade'] ?? "N\A" ) . "\n";
    }

    file_put_contents($name, $CSV);

    $name = getcwd() . "/" . $name;

    echo "\n\033[32mGradebook export finished successfully. Saved at location: '$name' \033[0m\n";
    if (CLIInputManagerObject::promptYN("Do you want to open up the directory? (y/n)")){
        open(getcwd());
    }
    goto label_submission_list;
}elseif($option === 'c'){
    $show_comments = !$show_comments;
    goto label_submission_list;
}elseif($option === 'quit' || $option == 0){
    system('clear');
    echo "Saving...\n";
    file_put_contents("$parent_dir/progress.json", json_encode($files_structured));
    echo "Good Bye!\n";
}else{
    file_put_contents("$parent_dir/progress.json", json_encode($files_structured));
    $selected = get_from_list($files_structured, $option);
    label_submission_option:
    system('clear');
    chdir($parent_dir);
    file_put_contents("$parent_dir/progress.json", json_encode($files_structured));
    echo "----------Selected:---------------------------------------------------------------------------------------------------\n";
    echo "$option: " . show_file($selected) . "\n";
    echo show_file_comments($selected) . "\n\n Deductions: \n";
    echo show_file_deductions($selected);
    echo "\n----------------------------------------------------------------------------------------------------------------------\n";
    echo "\n1. \033[35m[Open Submission in Grader] \033[0m";
    echo "\n2. \033[35m[Change Submission Grade] \033[0m";
    echo "\n3. \033[35m[Add Deduction/Addition] \033[0m";
    echo "\n4. \033[35m[Add Submission Comment] \033[0m";
    echo "\n5. \033[35m[Move to Workbench] \033[0m";
    echo "\n6. \033[35m[Open Workbench in File Browser] \033[0m";
    echo "\n7. \033[31m[Delete submission from save] \033[0m";

    echo "\n\n0. \033[33m[<- Back] \033[0m";
    echo "\nSelect an option 0-7: ";
    $option_list = CLIInputManagerObject::getInputLine();
    echo "----------------------------------------------------------\n";
    if (!is_numeric($option_list) || $option_list < 0 || $option_list > 7 ){
        show_error("INVALID INPUT Please select a number in range.");
        sleep(1);

        goto label_submission_option;
    }
    switch($option_list){
        case 0:
            goto label_submission_list;
            break;
        case 1:
            system('clear');
            echo "Moving into workbench...\n";
            $workbench_dir = make_workbench($parent_dir);
            set_up($selected['file_name'], $workbench_dir, $parent_dir, $flatten, $auto_make);
            chdir($workbench_dir);
            echo "----------NOTICE:---------------------------------------------------------------------------------------------------\n";
            echo "You are now in a partitioned environment with only this submission.\n";
            echo "You can run any commands that you would run in the terminal (like 'ls -l') by just typing them. \nTo read the README file, type 'readme'. \nTo change the grade for this assignment, type 'grade'. \nAdd a deduction: 'd'\nTo exit this environment, type 'exit', '0' or 'quit'.\nTo grade and exit, type 'gq'.\n Add a comment: 'c'\n";
            echo "--------------------------------------------------------------------------------------------------------------------\n";
            $hold = true;
            while($hold){
                echo "\n>>";
                $command = CLIInputManagerObject::getInputLine();
                echo "\n";
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
                    case "c":
                    case "comment":
                        goto label_submission_add_comment;
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
                    case "d":
                        echo "Deduction/Addition amount (may be negative) ";
                        $grade = CLIInputManagerObject::getInputLine();
                        if (!is_numeric($grade) ){
                            show_error("INVALID INPUT Please select a number in range. (Press enter to continue)");
                            CLIInputManagerObject::getInputLine();
                            goto label_submission_changegrade_option;
                        }
                        echo "Deduction/Addition comment ";
                        $comment = CLIInputManagerObject::getInputLine();
                        if(addDeduction($files_structured[$selected['file_name']], $grade, $comment)){
                            echo "\n\033[32mDeduction/Addition added successfully.\033[0m\n";
                        }else{
                            echo "\nDeduction/Addition added could not be added.\n";
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
            sleep(1);
            goto label_submission_option;
            break;
        case 3:
            echo "Deduction/Addition amount (may be negative) ";
            $grade = CLIInputManagerObject::getInputLine();
            if (!is_numeric($grade) ){
                show_error("INVALID INPUT Please select a number in range. (Press enter to continue)");
                CLIInputManagerObject::getInputLine();
                goto label_submission_changegrade_option;
            }
            echo "Deduction/Addition comment ";
            $comment = CLIInputManagerObject::getInputLine();
            if(addDeduction($files_structured[$selected['file_name']], $grade, $comment)){
                echo "\n\033[32mDeduction/Addition added successfully.\033[0m\n";
            }else{
                echo "\nDeduction/Addition added could not be added.\n";
            }
            sleep(1);
            goto label_submission_option;
            break;
        case 4:
            label_submission_add_comment:
            echo "Add a comment to this submission: ";
            $comment = CLIInputManagerObject::getInputLine();
            if(addComment($files_structured[$selected['file_name']], $comment)){
                echo "\n\033[32mComment changed successfully.\033[0m\n";
            }else{
                echo "\nComment could not be changed.\n";
            }
            sleep(1);
            goto label_submission_option;
            break;
        case 5:
            echo "Moving submission to a new workbench...\n";
            $workbench_dir = make_workbench($parent_dir);
            set_up($selected['file_name'], $workbench_dir, $parent_dir, $flatten, $auto_make);
            goto label_submission_option;
            break;
        case 6:
            echo "Attempting to open file manager...\n";
            open($workbench_dir);
            sleep(1);
            goto label_submission_option;
            break;
        case 7:
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

function change_grade(&$files_structured, $grade, $comment=null){
    $files_structured['grade'] = $grade;
    $files_structured['grade_time'] = time();
    return true;
}

function addComment(&$files_structured, $comment){
    $files_structured['comment'] = $comment;
    return true;
}
function addDeduction(&$files_structured, $amount,$comment){
    if (!isset($files_structured['deductions'])){
        $files_structured['deductions'] = [];
    }
    $files_structured['deductions'][] = [
        "amount" => $amount,
        "comment" => $comment
    ];
    change_grade($files_structured, $files_structured['grade'] + $amount);
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
    shell_exec("unzip ".($flatten ? '-jo' : '')." '../$file_name'");
    if ($auto_make){
        echo "----------MAKE:-----------------------------------------------------------------------------------------------------\n";
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
    return "\033[31m" . $file['student_name'] . " \033[0m - \033[36m[".(
    "" . (isset($file['grade']) ? ($file['grade'] .' @ '. (isset($file['grade_time']) ? date('Y-m-d H:i:s', $file['grade_time']) : 'N/A')) : "\033[34m -Not yet graded- \033[0m")
    )."\033[36m]\033[0m".($file['late'] === true ? '[LATE]' : "")." | (ID: " . $file['student_id'] . ") (" . $file['file_name'] . " @ \033[96m".date('m/d/Y H:i:s', filemtime($file['file_name'])).""."\033[0m)" ;
}
function show_file_comments($file){
    return "\033[31m Comments for " . $file['student_name'] . "'s submission \033[0m - \033[36m". ($file['comment'] ?? 'No comment.' . " - Grade: " . (isset($file['grade']) ? ($file['grade'] .' @ '. (isset($file['grade_time']) ? date('Y-m-d H:i:s', $file['grade_time']) : 'N/A')) : "\033[34m Not yet graded \033[0m"))  ;
}

function show_file_deductions($file){
    $string = "";
    foreach($file['deductions'] as $deduction){
        $string .= $deduction['amount'] . " Points: " . $deduction['comment'] . "\n";
    }
    return $string ;
}

function open($fd_name){
    shell_exec("open $fd_name  > /dev/null 2>/dev/null &");
    shell_exec("xdg-open $fd_name > /dev/null 2>/dev/null &");
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