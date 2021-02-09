<?php
echo "\nThis looks like the first time running SpeedGrader! Let's get you set up.\n";
echo "[SETUP][1] What is your name?\n";
$name = CLIInputManagerObject::getInputLine();
echo "[SETUP][1] Saving...\n";
SpeedGraderObject::setName($name);
echo "[SETUP][2] Alright, " . SpeedGraderObject::getName() . "! Where would you like for us to save our supporting files. \n Keep in mind, that this directory will include submissions, student names, and grades (unless otherwise specified). [Press enter for default in current working directory: 'speedgrader/supporting']\n";
$directory = CLIInputManagerObject::getInputLine();
SpeedGraderObject::setSupportingDirectory($directory);
echo "[SETUP][2] Saving...\n";
echo "[SETUP] Setup has finished. You're good to get grading! (Press enter to continue)\n";
CLIInputManagerObject::getInputLine();