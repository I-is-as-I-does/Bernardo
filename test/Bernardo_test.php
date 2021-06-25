<?php

function rBool()
{
    return (bool) random_int(0, 1);
}

function rMinLen()
{
    return random_int(4, 8);
}

function formatEcho($it)
{
    if (is_array($it)) {
        return implode(', ', $it);
    }
    if (is_bool($it)) {
        if (!$it) {
            return 'false';
        }
        return 'true';
    }
    if ($it === null || $it === '') {
        return 'null';
    }
    return $it;
}

$entries = ['master', 'admino', 'www', 'shittyshit', '_g', 'a-a', 'zorro-friend', 'ZorroZorro', 'Koons', 'vas Qwib-Qwib', 'c\'est la fête!'];
$methods = [
    'isValidSubDomain' => ['returnSuggestion' => rBool(), 'strict' => rBool(), 'minLen' => rMinLen()],
    'isValidUsername' => ['returnSuggestion' => rBool(), 'strict' => rBool(), 'minLen' => rMinLen()],
    'formatSubdomain' => ['minLen' => rMinLen()],
    'formatUsername' => ['minLen' => rMinLen()],
    'replaceDiacr' => [],
    'forceLength' => ['minLen' => rMinLen()],
    'format' => ['\s+', 'toLower' => rBool(), 'minLen' => rMinLen()],
    'strictExtractForbidden' => ['stopAtFirst' => rBool()],
    'extractForbidden' => [],
    'cleanEntry' => ['matches' => ['Koons']],
    'isValid' => ['strict' => rBool()]];

require dirname(__DIR__ ). '/src/Bernardo_i.php';
require dirname(__DIR__) . '/src/Bernardo.php';

$Bernardo = new SSITU\Bernado\Bernardo();
$adtDictionnary = ['qwibqwib', 'jeffkoons'];
$cstmBernardo = new SSITU\Bernado\Bernardo($adtDictionnary);

foreach (['Bernardo' => $Bernardo, 'cstmBernardo' => $cstmBernardo] as $k => $B) {
    echo '# ' . $k . '<br>' . '<br>';
    foreach ($entries as $entry) {
        echo 'entry: ' . $entry . '<br>';

        foreach ($methods as $method => $argm) {

            echo '.... method: ' . $method . '<br>';
            echo '.......... argm: ';
            foreach ($argm as $name => $opt) {
                echo $name . ' = ' . formatEcho($opt) . '; ';
            }
            echo '<br>';
            array_unshift($argm, $entry);
            $job = $B->$method(...$argm);
            echo '.......... rslt: ' . formatEcho($job) . '<br>';
        }

        echo '<br>';
    }
    echo '<br>';
}
