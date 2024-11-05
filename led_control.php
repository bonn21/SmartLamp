<?php
if (isset($_GET['action'])) 
{
    $action = $_GET['action'];

    if (!in_array($action, ['on', 'auto', 'apply'])) {
        die("Invalid action");
    }
    elseif ($action == 'on') {
        $output = shell_exec('sudo /var/www/html/run_dht22.sh 2>&1'); 
        echo $output;
    } 
    elseif ($action == 'auto') {
        $output = shell_exec('sudo /var/www/html/run_lcd.sh 2>&1'); 
        echo $output;
    } 
    elseif ($action == 'apply') {
        $output = shell_exec('sudo /var/www/html/run_ledrgb.sh 2>&1'); 
        echo $output;
    }
}
?>