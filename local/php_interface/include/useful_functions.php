<?
if (!function_exists('getDateTime')) {
    function getDateTime(): string
    {
        return (new DateTime())->format("c");
    }
}
if (!function_exists('writeToLog')) {
    function writeToLog($var, string $logName, ?string $dirPath = null)
    {
        if(empty($dirPath)) {
            $dirPath = __DIR__ . "/logs/";
        }

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0775);
        }

        $data = (is_array($var) || is_object($var)) ? print_r($var, true) . "\n" : $var . "\n";
        file_put_contents($dirPath . $logName . '.txt', $data, FILE_APPEND);
    }
}
?>