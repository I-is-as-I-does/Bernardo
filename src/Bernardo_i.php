<?php

namespace SSITU\Bernardo;

interface Bernardo_i
{
    public function __construct($adtDictionnary = array());

    public function setAdtDictionnary(array $items);

     # All-in-one methods:
     
    public function isValidSubDomain($entry, $returnSuggestion = false, $strict = true, $minLen = 4, $maxLen = 20);
    public function isValidUsername($entry, $returnSuggestion = false, $strict = true, $minLen = 4, $maxLen = 20);

    # Cherry-pick methods:

    // These 4 methods will handle diacritics substitution:
    public function formatSubdomain($entry, $minLen = 4, $maxLen = 20);
    public function formatUsername($entry, $minLen = 4, $maxLen = 20);
    public function format($entry, $pattern, $toLower = false, $minLen = 4, $maxLen = 20);
    public function replaceDiacr($entry);

    // For these 3, diacritics MUST have been handled beforehand (or expect possibly wrong returns):
    public function isValid($noDiacrEntry, $strict = true);
    public function strictExtractForbidden($noDiacrEntry, $stopAtFirst = false);
    public function extractForbidden($noDiacrEntry);

    // Adjustements:
    public function cleanEntry($entry, $matches);
    public function forceLength($entry, $minLen = 4, $maxLen = 20);   
    
}
