<?php
/**
* @link http://code.google.com/p/php-lzw/
* @author Jakub Vrana, http://www.vrana.cz/
* @copyright 2009 Jakub Vrana
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
*/

/** LZW compression
* @param string data to compress
* @return string binary data
*/
function lzw_compress($string) {
        // compression
        $dictionary = array_flip(range("\0", "\xFF"));
        $word = "";
        $codes = array();
        for ($i=0; $i <= strlen($string); $i++) {
                $x = substr($string, $i, 1);
                if (strlen($x) && isset($dictionary[$word . $x])) {
                        $word .= $x;
                } elseif ($i) {
                        $codes[] = $dictionary[$word];
                        $dictionary[$word . $x] = count($dictionary);
                        $word = $x;
                }
        }
       
        // convert codes to binary string
        $dictionary_count = 256;
        $bits = 8; // ceil(log($dictionary_count, 2))
        $return = "";
        $rest = 0;
        $rest_length = 0;
        foreach ($codes as $code) {
                $rest = ($rest << $bits) + $code;
                $rest_length += $bits;
                $dictionary_count++;
                if ($dictionary_count >> $bits) {
                        $bits++;
                }
                while ($rest_length > 7) {
                        $rest_length -= 8;
                        $return .= chr($rest >> $rest_length);
                        $rest &= (1 << $rest_length) - 1;
                }
        }
        return $return . ($rest_length ? chr($rest << (8 - $rest_length)) : "");
}

/** LZW decompression
* @param string compressed binary data
* @return string original data
*/
function lzw_decompress($binary) {
    $dictionary_count = 256;
    $bits = 8;
    $codes = array();
    $rest = 0;
    $rest_length = 0;
    
    mb_internal_encoding("UTF-8"); 
    for ($i = 0; $i < mb_strlen($binary); $i++ ) {$codes[] = mb_ord(mb_substr($binary, $i, 1)); }
        
    // decompression
    $dictionary = range("\0", "\xFF");
    $return = "";
    foreach ($codes as $i => $code) {
        $element = $dictionary[$code];
        if (!isset($element)) $element = $word . $word[0];
        $return .= $element;
        if ($i) $dictionary[] = $word . $element[0];    		
        $word = $element;
    }
    return $return;
}

function mb_ord($string) {
    if (extension_loaded('mbstring') === true) {
        mb_language('Neutral');
        mb_internal_encoding('UTF-8');
        mb_detect_order(array('UTF-8', 'ISO-8859-15', 'ISO-8859-1', 'ASCII'));
        $result = unpack('N', mb_convert_encoding($string, 'UCS-4BE', 'UTF-8'));
        if (is_array($result) === true) return $result[1];
    }
    return ord($string);
}
