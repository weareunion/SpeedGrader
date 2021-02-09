echo "Installing SpeedGrader... Requires php!"
FILE=SpeedGrader.php
if test -f "$FILE"; then
    mkdir -p ~/SpeedGrader
     rsync -av --delete src/ ~/SpeedGrader/src
    cp -fr SpeedGrader.php ~/SpeedGrader
    echo "You're good to go! Just run php ~/SpeedGrader/SpeedGrader.php [quick (for quick grading)] [directory to grade]"
    exit 0;
    else
    echo "[ERROR] This installer must be run in the same directory as SpeedGrader.php and the src directory!"
fi

