<?php


class CLIInputManagerObject
{
    static function getInputLine(){
        echo "\033[35m";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        fclose($handle);
        echo "\033[0m";
        return trim($line);
    }

    static function promptYN($prompt="Please enter 'y' for yes or 'n' for no: "){
        echo "[RESPOND] " . $prompt;

        while($line !== 'y' && $line !== 'n'){
            $handle = fopen ("php://stdin","r");
            $line = trim(fgets($handle));
            fclose($handle);
            if ($line == 'y') {
                return true;
            }
            if ($line == 'n') {
                return false;
            }
            echo "Incorrect input. Please try again.\n";
        }
    }
}