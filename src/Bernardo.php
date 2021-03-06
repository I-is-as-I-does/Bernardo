<?php

namespace SSITU\Bernardo;

class Bernardo implements Bernardo_i {

    private $dictionnary = [];
    private $diacritics;

    public function __construct($adtDictionnary = array())
    {
        $this->dictionnary = json_decode(file_get_contents(dirname(__DIR__) . '/resources/bernardo-dictionnary.json'), true);
        $this->diacritics = json_decode(file_get_contents(dirname(__DIR__) . '/resources/bernardo-diacritics.json'), true);
        if($adtDictionnary){
            $this->setAdtDictionnary($adtDictionnary);
        }

    }

    public function setAdtDictionnary(array $items)
    {
        foreach ($items as $itm) {
            if (is_string($itm) && !in_array($itm, $this->dictionnary)) {
                $this->dictionnary[] = $itm;
            }
        }
    }

    # All-in-one methods:

    public function isValidSubDomain($entry, $returnSuggestion = false, $strict = true, $minLen = 4, $maxLen = 20)
    {
        return $this->formatAndValidate($entry, true, $returnSuggestion, $strict, $minLen, $maxLen);
    }

    public function isValidUsername($entry, $returnSuggestion = false, $strict = true, $minLen = 4, $maxLen = 20)
    {
        return $this->formatAndValidate($entry, false, $returnSuggestion, $strict, $minLen, $maxLen);

    }

    # Cherry-pick methods:

    // These 4 methods will handle diacritics substitution:
    public function formatSubdomain($entry, $minLen = 4, $maxLen = 20)
    {
        $pattern = '^-*|[^a-z-]+|-*$';
        return $this->format($entry, $pattern, true, $minLen, $maxLen);

    }

    public function formatUsername($entry, $minLen = 4, $maxLen = 20)
    {
        $pattern = '^[-_.]*|[^\w.-]+|[-_.]*$';
        return $this->format($entry, $pattern, false, $minLen, $maxLen);
    }

    
    public function format($entry, $pattern, $toLower = false, $minLen = 4, $maxLen = 20)
    {
        if ($toLower) {
            $entry = strtolower($entry);
        }
        $entry = preg_replace('/'.$pattern.'/', '', $this->replaceDiacr($entry));
        return $this->forceLength($entry, $minLen, $maxLen);
    }

    public function replaceDiacr($entry)
    {
        foreach ($this->diacritics as $diacr => $replc) {
            $entry = str_replace($diacr, $replc, $entry);
        }
        return $entry;
    }
    
   // For these 3, diacritics MUST have been handled beforehand (or expect possibly wrong returns):

    public function isValid($noDiacrEntry, $strict = true)
    {
        if ($strict) {
            $match = $this->strictExtractForbidden($noDiacrEntry, true);
        } else {
            $match = $this->extractForbidden($noDiacrEntry);
        }

        if ($match === false) {
            return true;
        }
        return false;
    }

    public function strictExtractForbidden($noDiacrEntry, $stopAtFirst = false)
    {

        $match = $this->extractForbidden($noDiacrEntry);
        if ($match !== false) {
            return $match;
        }
        $matches = [];
        $noDiacrEntry = $this->minimalEscp($noDiacrEntry);
        $entrylen = strlen($noDiacrEntry);
        foreach ($this->dictionnary as $word) {
            if (strlen($word) <= $entrylen) {
                $expl = explode($word, $noDiacrEntry);
                if (count($expl) > 1) {
                    if ($stopAtFirst === false) {
                        return $word;
                    }
                    $matches[] = $word;
                }
            }
        }
        if (empty($matches)) {
            return false;
        }
        return $matches;
    }

    public function extractForbidden($noDiacrEntry)
    {
        $noDiacrEntry = $this->minimalEscp($noDiacrEntry);
        $search = array_search($noDiacrEntry, $this->dictionnary);
        if ($search !== false) {
            return $this->dictionnary[$search];
        }
        return false;
    }

    // Adjustements:
    public function cleanEntry($entry, $matches)
    {
        if ($matches !== false) {
            if (!is_array($matches)) {
                $matches = [$matches];
            }
            foreach ($matches as $mt) {
                if (empty($entry)) {
                    break;
                }
                $entry = str_replace($mt, '', $entry);
            }
        }
        return $entry;
    }

    public function forceLength($entry, $minLen = 4, $maxLen = 20)
    {   
        if(strlen($entry) > $maxLen){
            return substr($entry, 0, $maxLen);
        }
        while (strlen($entry) < $minLen) {
            $entry .= $entry[random_int(1,strlen($entry))-1];
        }
        return $entry;
    }
    
    # Private
    private function minimalEscp($noDiacrEntry){
        return preg_replace('/\s+/','',strtolower($noDiacrEntry));
    }

    private function formatAndValidate($entry, $isSubdomain = false, $returnSuggestion = false, $strict = true,  $minLen = 4, $maxLen = 20)
    {
        $method = 'formatUsername';
        if ($isSubdomain) {
            $method = 'formatSubdomain';
        }
        $formattedEntry = $this->$method($entry, $minLen, $maxLen);
        if ($formattedEntry != $entry) {
            if ($returnSuggestion) {
                return $formattedEntry;
            }
            return false;
        }

        if (!$returnSuggestion) {
            return $this->isValid($formattedEntry, $strict);
        }

        if ($strict) {
            $match = $this->strictExtractForbidden($formattedEntry, false);
        } else {
            $match = $this->extractForbidden($formattedEntry);
        }
        if ($match === false) {
            return true;
        }
        return $this->cleanEntry($formattedEntry, $match);
    }

}
