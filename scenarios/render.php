<?php
    define("SCENARIOS_PATH", __dir__);
    define("SCENARIO_CACHE", __dir__ . "/../scenario.cache");

    if(!is_dir(SCENARIOS_PATH)) {
        fwrite(STDERR, "'" . SCENARIOS_PATH . "' is not a directory.\n");
        exit(1);
    }

    if(!file_exists(SCENARIO_CACHE)) {
        fwrite(STDERR, "'" . SCENARIO_CACHE . "' file does not exist.\n");
        exit(1);
    }

    //Get the contents of SCENARIO_CACHE
    $scenarios = file_get_contents(SCENARIO_CACHE);
    $scenarios = json_decode($scenarios);

    if($scenarios === null) {
        fwrite(STDERR, "Failed to json decode file '" . SCENARIO_CACHE . "'.\n");
        exit(1);
    }

    //Loop through each scenario directory
    if($handle = opendir(SCENARIOS_PATH)) {
        while(false !== ($current = readdir($handle))) {
           if($current !== "." && $current !== ".." && is_dir(SCENARIOS_PATH . "/" . $current)) {
                echo "\nReading [" . SCENARIOS_PATH . "/" . $current . "]";

                $lines = @file(SCENARIOS_PATH . "/" . $current . "/php.mako");

                if($lines) {
                    echo "\n\t=> php.mako found <=\n";

                    foreach($lines as $number => $line) {
                        $line = trim($line);

                        if(false !== strpos($line, "% if mode == 'definition':")) {
                           if(isset($lines[$number + 1])) {
                                echo "\t\tWriting [" . SCENARIOS_PATH . "/" . $current . "/php.definition]\n";
                                file_put_contents(SCENARIOS_PATH . "/" . $current . "/definition.php", trim($lines[$number + 1]));
                           }

                           continue;
                        }

                        if(false !== strpos($line, '${main.php_boilerplate()}')) {
                            if(isset($line[$number + 1])) {
                                $request = '';
                                for($i = ($number + 1); $i < count($lines); $i++) {
                                    $request .= $lines[$i];
                                }

                                $request = str_replace('% endif', '', $request);

                                echo "\t\tWriting [" . SCENARIOS_PATH . "/" . $current . "/php.request]\n";
                                file_put_contents(SCENARIOS_PATH . "/" . $current . "/request.php", trim($request));
                            }

                            continue;
                        }
                    }
                }
            }
        }
    }
?>