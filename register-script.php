<?php

# region Func
function after($string, $needle, $offset = 0){
    $pos = mb_strpos($string, $needle, $offset);
    if ($pos === FALSE){
        return $string;
    }

    return mb_substr($string, $pos + mb_strlen($needle));
}
function afterLast($string, $needle, $offset = 0){
    $pos = mb_strrpos($string, $needle, $offset);
    if ($pos === FALSE){
        return $string;
    }

    return mb_substr($string, $pos + mb_strlen($needle));
}
function before($string, $needle){
    $pos = mb_strpos($string, $needle);
    if ($pos === FALSE){
        return $string;
    }

    return mb_substr($string, 0, $pos);
}
function beforeLast($string, $needle){
    $pos = mb_strrpos($string, $needle);
    if ($pos === FALSE){
        return $string;
    }

    return mb_substr($string, 0, $pos);
}

function paste_into_position($string, $position, $content)
{
    return mb_substr($string, 0, $position).
        $content.
        mb_substr($string, $position);
}

function getOffsetString($configContent)
{
    $beforeAppProvider = before($configContent, 'AppServiceProvider::class');
    $providerSingleString = afterLast($beforeAppProvider, "\n");

    preg_match('/^(\s*)/', $providerSingleString, $matches);
    $spacesCount = strlen($matches[1]);

    return str_pad('', $spacesCount);
}

function getCodeString($offsetString){
    $codeLines = [
        "/* ",
        " * Package LaraBridge: auto registered provider",
        " */",
        "Sitroz\LaraBridge\LaraBridgeServiceProvider::class,",
    ];
    return "\n\n".$offsetString.implode("\n".$offsetString, $codeLines);
}

function findPositionToPaste($configContent)
{
    $posAppProvider = mb_strpos($configContent, 'AppServiceProvider::class');
    $posCloseArray = mb_strpos($configContent, "]", $posAppProvider);

    $arrayPart = mb_substr($configContent, $posAppProvider, $posCloseArray-$posAppProvider);

    return $posAppProvider + mb_strrpos($arrayPart, "\n");
}

function getLineNumberAtPosition($string, $position)
{
    $contentBeforeInsert = substr($string, 0, $position);
    return substr_count($contentBeforeInsert, "\n") + 1;
}

function assertArtisanRegisteredCommand($laravelPath){
    $artisanPath = $laravelPath.DIRECTORY_SEPARATOR.'artisan';
    $command = '"'.PHP_BINARY.'"' . ' ' . $artisanPath . ' list';
    exec($command, $output, $return_var);

    if ($return_var !== 0){
        throw new \Exception('Artisan broken');
    }

    $isMatch = FALSE;
    foreach ($output as $string) {
        if (strpos($string, 'laraBridge')) {
            $isMatch = TRUE;
            break;
        }
    }

    return $isMatch;
}

#endregion

$vendorPath = before(__DIR__, DIRECTORY_SEPARATOR.'sitroz');
$laravelPath = beforeLast($vendorPath, DIRECTORY_SEPARATOR);

// make sure laravel works and changes required
try {
    if (assertArtisanRegisteredCommand($laravelPath)) {
        echo 'LaraBridgeServiceProvider already registered.' . "\n";
        echo "No changes need.";
        exit(0);
    }
} catch (Exception $e) {
    echo 'Something went wrong. Unable to modify config file in auto mode.'."\n";
    exit($e->getMessage());
}

# region modifying content

$appConfigPath = $laravelPath.
    DIRECTORY_SEPARATOR.'config'.
    DIRECTORY_SEPARATOR.'app.php';

$configContent = file_get_contents($appConfigPath);

$position = findPositionToPaste($configContent);

$spacesOffset = getOffsetString($configContent);
$codeString = getCodeString($spacesOffset);

$modifiedConfig = paste_into_position($configContent, $position, $codeString);

$line = getLineNumberAtPosition($configContent, $position);
echo "The code was pasted to \nFile : " .$appConfigPath. "\nLine : " . ($line+2);


file_put_contents($appConfigPath, $modifiedConfig);

#endregion

# region test and rollback if it needs

try {
    if (!assertArtisanRegisteredCommand($laravelPath)){
        echo 'Something went wrong. Unable to modify config file in auto mode.'."\n";
        echo "Revert changes back";
        file_put_contents($appConfigPath, $configContent);
    }
} catch (Exception $e) {
    echo 'Something went wrong. Unable to modify config file in auto mode.'."\n";
    echo "Revert changes back";
}

# endregion