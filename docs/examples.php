<?php

$time = microtime(true);

for ($i=1; $i <= 9; $i++) {
    echo "tutorial_example_0{$i}:\n";
    echo "====================\n";
    include __DIR__ . "/tutorial_example_0{$i}.php";

    echo "\n----------------------------------\n\n";
}


echo "Time: " . round(microtime(true) - $time, 5);
