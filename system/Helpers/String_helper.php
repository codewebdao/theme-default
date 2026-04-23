<?php
	
function mbstring_binary_safe_encoding( $reset = false ) {
	static $encodings  = array();
	static $overloaded = null;

	if ( is_null( $overloaded ) ) {
		if ( function_exists( 'mb_internal_encoding' )
			&& ( (int) ini_get( 'mbstring.func_overload' ) & 2 ) // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.mbstring_func_overloadDeprecated
		) {
			$overloaded = true;
		} else {
			$overloaded = false;
		}
	}

	if ( false === $overloaded ) {
		return;
	}

	if ( ! $reset ) {
		$encoding = mb_internal_encoding();
		array_push( $encodings, $encoding );
		mb_internal_encoding( 'ISO-8859-1' );
	}

	if ( $reset && $encodings ) {
		$encoding = array_pop( $encodings );
		mb_internal_encoding( $encoding );
	}
}

function reset_mbstring_encoding() {
	mbstring_binary_safe_encoding( true );
}


function seems_utf8( $str ) {
	mbstring_binary_safe_encoding();
	$length = strlen( $str );
	reset_mbstring_encoding();

	for ( $i = 0; $i < $length; $i++ ) {
		$c = ord( $str[ $i ] );

		if ( $c < 0x80 ) {
			$n = 0; // 0bbbbbbb
		} elseif ( ( $c & 0xE0 ) === 0xC0 ) {
			$n = 1; // 110bbbbb
		} elseif ( ( $c & 0xF0 ) === 0xE0 ) {
			$n = 2; // 1110bbbb
		} elseif ( ( $c & 0xF8 ) === 0xF0 ) {
			$n = 3; // 11110bbb
		} elseif ( ( $c & 0xFC ) === 0xF8 ) {
			$n = 4; // 111110bb
		} elseif ( ( $c & 0xFE ) === 0xFC ) {
			$n = 5; // 1111110b
		} else {
			return false; // Does not match any model.
		}

		for ( $j = 0; $j < $n; $j++ ) { // n bytes matching 10bbbbbb follow ?
			if ( ( ++$i === $length ) || ( ( ord( $str[ $i ] ) & 0xC0 ) !== 0x80 ) ) {
				return false;
			}
		}
	}

	return true;
}


if ( ! function_exists( 'str_starts_with' ) ) {
    function str_starts_with( $haystack, $needle ) {
        return substr( $haystack, 0, strlen( $needle ) ) === $needle;
    }
}

if ( ! function_exists( 'time_compare' ) ) {
    function time_compare( $time1, $time2 ) {
        // Convert time strings to Unix timestamps
        $timestamp1 = strtotime( $time1 );
        $timestamp2 = strtotime( $time2 );
        
        return ( $timestamp1 < $timestamp2 ) ? true : false;
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $string ) {
        return htmlspecialchars( $string, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $string ) {
        return htmlspecialchars( $string, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    }
}

if ( ! function_exists( 'get_locale' ) ) {
    function get_locale() {
        // 1. Check if user's locale is stored in cookie.
        // If exists and not empty, use this value.
        if ( isset( $_COOKIE['locale'] ) && ! empty( $_COOKIE['locale'] ) ) {
            return $_COOKIE['locale'];
        }
        
        // 2. Check if user's locale is stored in session.
        // This is useful when you have determined locale elsewhere in the application.
        if ( \System\Libraries\Session::has('locale') ) {
            return \System\Libraries\Session::get('locale');
        }
        
        // 3. If no information in cookie or session,
        // check HTTP_ACCEPT_APP_LANGUAGE header sent by browser.
        if ( isset( $_SERVER['HTTP_ACCEPT_APP_LANGUAGE'] ) ) {
            // This header usually contains string of preferred languages, example: "en-US,en;q=0.9,vi;q=0.8"
            $langs = explode( ',', $_SERVER['HTTP_ACCEPT_APP_LANGUAGE'] );
            if ( ! empty( $langs ) ) {
                // Get first element, which is the highest priority language.
                $locale = strtolower( trim( $langs[0] ) );
                // Normalize: replace '-' with '_' (example: "en-us" -> "en_us")
                $locale = str_replace( '-', '_', $locale );
                // If locale has "language_country" format (example: "en_us"), convert country part to uppercase.
                $parts = explode( '_', $locale );
                if ( count( $parts ) === 2 ) {
                    $locale = $parts[0] . '_' . strtoupper( $parts[1] );
                }
                return $locale;
            }
        }
        
        // 4. If cannot determine locale from above sources, return default value.
        return 'en_US';
    }
}

function remove_accents( $text, $locale = '' ) {
    // Handle null or non-string input
    if ($text === null || !is_string($text)) {
        return $text === null ? '' : (string)$text;
    }
    
    // If string doesn't contain accented characters, return immediately
    if ( ! preg_match( '/[\x80-\xff]/', $text ) ) {
        return $text;
    }
    
    if ( seems_utf8( $text ) ) {
        // Normalize Unicode (from NFD to NFC) if possible
        if ( function_exists( 'normalizer_is_normalized' ) && function_exists( 'normalizer_normalize' ) ) {
            if ( ! normalizer_is_normalized( $text ) ) {
                $text = normalizer_normalize( $text );
            }
        }
        
        
        // --- Merge maps: priority order Map3 > Map2 > Map1 ---
        $merged_map = array(

            "ª" => "a",
            "º" => "o",
            "À" => "A",
            "Á" => "A",
            "Â" => "A",
            "Ã" => "A",
            "Ä" => "A",
            "Å" => "A",
            "Æ" => "AE",
            "Ç" => "C",
            "È" => "E",
            "É" => "E",
            "Ê" => "E",
            "Ë" => "E",
            "Ì" => "I",
            "Í" => "I",
            "Î" => "I",
            "Ï" => "I",
            "Ð" => "D",
            "Ñ" => "N",
            "Ò" => "O",
            "Ó" => "O",
            "Ô" => "O",
            "Õ" => "O",
            "Ö" => "O",
            "Ù" => "U",
            "Ú" => "U",
            "Û" => "U",
            "Ü" => "U",
            "Ý" => "Y",
            "Þ" => "TH",
            "ß" => "B",
            "à" => "a",
            "á" => "a",
            "â" => "a",
            "ã" => "a",
            "ä" => "a",
            "å" => "a",
            "æ" => "ae",
            "ç" => "c",
            "è" => "e",
            "é" => "e",
            "ê" => "e",
            "ë" => "e",
            "ì" => "i",
            "í" => "i",
            "î" => "i",
            "ï" => "i",
            "ð" => "d",
            "ñ" => "n",
            "ò" => "o",
            "ó" => "o",
            "ô" => "o",
            "õ" => "o",
            "ö" => "o",
            "ø" => "o",
            "ù" => "u",
            "ú" => "u",
            "û" => "u",
            "ü" => "u",
            "ý" => "y",
            "þ" => "th",
            "ÿ" => "y",
            "Ø" => "O",
            "Ā" => "A",
            "ā" => "a",
            "Ă" => "A",
            "ă" => "a",
            "Ą" => "A",
            "ą" => "a",
            "Ć" => "C",
            "ć" => "c",
            "Ĉ" => "C",
            "ĉ" => "c",
            "Ċ" => "C",
            "ċ" => "c",
            "Č" => "C",
            "č" => "c",
            "Ď" => "D",
            "ď" => "d",
            "Đ" => "D",
            "đ" => "d",
            "Ē" => "E",
            "ē" => "e",
            "Ĕ" => "E",
            "ĕ" => "e",
            "Ė" => "E",
            "ė" => "e",
            "Ę" => "E",
            "ę" => "e",
            "Ě" => "E",
            "ě" => "e",
            "Ĝ" => "G",
            "ĝ" => "g",
            "Ğ" => "G",
            "ğ" => "g",
            "Ġ" => "G",
            "ġ" => "g",
            "Ģ" => "G",
            "ģ" => "g",
            "Ĥ" => "H",
            "ĥ" => "h",
            "Ħ" => "H",
            "ħ" => "h",
            "Ĩ" => "I",
            "ĩ" => "i",
            "Ī" => "I",
            "ī" => "i",
            "Ĭ" => "I",
            "ĭ" => "i",
            "Į" => "I",
            "į" => "i",
            "İ" => "I",
            "ı" => "i",
            "Ĳ" => "IJ",
            "ĳ" => "ij",
            "Ĵ" => "J",
            "ĵ" => "j",
            "Ķ" => "K",
            "ķ" => "k",
            "ĸ" => "k",
            "Ĺ" => "L",
            "ĺ" => "l",
            "Ļ" => "L",
            "ļ" => "l",
            "Ľ" => "L",
            "ľ" => "l",
            "Ŀ" => "L",
            "ŀ" => "l",
            "Ł" => "l",
            "ł" => "l",
            "Ń" => "N",
            "ń" => "n",
            "Ņ" => "N",
            "ņ" => "n",
            "Ň" => "N",
            "ň" => "n",
            "ŉ" => "n",
            "Ŋ" => "N",
            "ŋ" => "n",
            "Ō" => "O",
            "ō" => "o",
            "Ŏ" => "O",
            "ŏ" => "o",
            "Ő" => "O",
            "ő" => "o",
            "Œ" => "OE",
            "œ" => "oe",
            "Ŕ" => "R",
            "ŕ" => "r",
            "Ŗ" => "R",
            "ŗ" => "r",
            "Ř" => "R",
            "ř" => "r",
            "Ś" => "S",
            "ś" => "s",
            "Ŝ" => "S",
            "ŝ" => "s",
            "Ş" => "S",
            "ş" => "s",
            "Š" => "S",
            "š" => "s",
            "Ţ" => "T",
            "ţ" => "t",
            "Ť" => "T",
            "ť" => "t",
            "Ŧ" => "T",
            "ŧ" => "t",
            "Ũ" => "U",
            "ũ" => "u",
            "Ū" => "U",
            "ū" => "u",
            "Ŭ" => "U",
            "ŭ" => "u",
            "Ů" => "U",
            "ů" => "u",
            "Ű" => "U",
            "ű" => "u",
            "Ų" => "U",
            "ų" => "u",
            "Ŵ" => "W",
            "ŵ" => "w",
            "Ŷ" => "Y",
            "ŷ" => "y",
            "Ÿ" => "Y",
            "Ź" => "Z",
            "ź" => "z",
            "Ż" => "Z",
            "ż" => "z",
            "Ž" => "Z",
            "ž" => "z",
            "ſ" => "s",
            "Ə" => "E",
            "ǝ" => "e",
            "Ș" => "S",
            "ș" => "s",
            "Ț" => "T",
            "ț" => "t",
            "€" => "E",
            "£" => "",
            "Ơ" => "O",
            "ơ" => "o",
            "Ư" => "U",
            "ư" => "u",
            "Ầ" => "A",
            "ầ" => "a",
            "Ằ" => "A",
            "ằ" => "a",
            "Ề" => "E",
            "ề" => "e",
            "Ồ" => "O",
            "ồ" => "o",
            "Ờ" => "O",
            "ờ" => "o",
            "Ừ" => "U",
            "ừ" => "u",
            "Ỳ" => "Y",
            "ỳ" => "y",
            "Ả" => "A",
            "ả" => "a",
            "Ẩ" => "A",
            "ẩ" => "a",
            "Ẳ" => "A",
            "ẳ" => "a",
            "Ẻ" => "E",
            "ẻ" => "e",
            "Ể" => "E",
            "ể" => "e",
            "Ỉ" => "I",
            "ỉ" => "i",
            "Ỏ" => "O",
            "ỏ" => "o",
            "Ổ" => "O",
            "ổ" => "o",
            "Ở" => "O",
            "ở" => "o",
            "Ủ" => "U",
            "ủ" => "u",
            "Ử" => "U",
            "ử" => "u",
            "Ỷ" => "Y",
            "ỷ" => "y",
            "Ẫ" => "A",
            "ẫ" => "a",
            "Ẵ" => "A",
            "ẵ" => "a",
            "Ẽ" => "E",
            "ẽ" => "e",
            "Ễ" => "E",
            "ễ" => "e",
            "Ỗ" => "O",
            "ỗ" => "o",
            "Ỡ" => "O",
            "ỡ" => "o",
            "Ữ" => "U",
            "ữ" => "u",
            "Ỹ" => "Y",
            "ỹ" => "y",
            "Ấ" => "A",
            "ấ" => "a",
            "Ắ" => "A",
            "ắ" => "a",
            "Ế" => "E",
            "ế" => "e",
            "Ố" => "O",
            "ố" => "o",
            "Ớ" => "O",
            "ớ" => "o",
            "Ứ" => "U",
            "ứ" => "u",
            "Ạ" => "A",
            "ạ" => "a",
            "Ậ" => "A",
            "ậ" => "a",
            "Ặ" => "A",
            "ặ" => "a",
            "Ẹ" => "E",
            "ẹ" => "e",
            "Ệ" => "E",
            "ệ" => "e",
            "Ị" => "I",
            "ị" => "i",
            "Ọ" => "O",
            "ọ" => "o",
            "Ộ" => "O",
            "ộ" => "o",
            "Ợ" => "O",
            "ợ" => "o",
            "Ụ" => "U",
            "ụ" => "u",
            "Ự" => "U",
            "ự" => "u",
            "Ỵ" => "Y",
            "ỵ" => "y",
            "ɑ" => "a",
            "Ǖ" => "U",
            "ǖ" => "u",
            "Ǘ" => "U",
            "ǘ" => "u",
            "Ǎ" => "A",
            "ǎ" => "a",
            "Ǐ" => "I",
            "ǐ" => "i",
            "Ǒ" => "O",
            "ǒ" => "o",
            "Ǔ" => "U",
            "ǔ" => "u",
            "Ǚ" => "U",
            "ǚ" => "u",
            "Ǜ" => "U",
            "ǜ" => "u",
            "©" => "(c)",
            "Α" => "A",
            "Β" => "B",
            "Γ" => "G",
            "Δ" => "D",
            "Ε" => "E",
            "Ζ" => "Z",
            "Η" => "H",
            "Θ" => "8",
            "Ι" => "I",
            "Κ" => "K",
            "Λ" => "L",
            "Μ" => "M",
            "Ν" => "N",
            "Ξ" => "3",
            "Ο" => "O",
            "Π" => "P",
            "Ρ" => "P",
            "Σ" => "S",
            "Τ" => "T",
            "Υ" => "Y",
            "Φ" => "F",
            "Χ" => "X",
            "Ψ" => "Y",
            "Ω" => "W",
            "Ά" => "A",
            "Έ" => "E",
            "Ί" => "I",
            "Ό" => "O",
            "Ύ" => "Y",
            "Ή" => "H",
            "Ώ" => "W",
            "Ϊ" => "I",
            "Ϋ" => "Y",
            "α" => "a",
            "β" => "b",
            "γ" => "g",
            "δ" => "d",
            "ε" => "e",
            "ζ" => "z",
            "η" => "h",
            "θ" => "8",
            "ι" => "i",
            "κ" => "k",
            "λ" => "l",
            "μ" => "m",
            "ν" => "n",
            "ξ" => "3",
            "ο" => "o",
            "π" => "p",
            "ρ" => "r",
            "σ" => "s",
            "τ" => "t",
            "υ" => "y",
            "φ" => "f",
            "χ" => "x",
            "ψ" => "Y",
            "ω" => "w",
            "ά" => "a",
            "έ" => "e",
            "ί" => "i",
            "ό" => "o",
            "ύ" => "y",
            "ή" => "h",
            "ώ" => "w",
            "ς" => "c",
            "ϊ" => "i",
            "ΰ" => "y",
            "ϋ" => "y",
            "ΐ" => "i",
            "А" => "A",
            "Б" => "B",
            "В" => "V",
            "Г" => "R",
            "Д" => "A",
            "Е" => "E",
            "Ё" => "Е",
            "Ж" => "X",
            "З" => "3",
            "И" => "I",
            "Й" => "И",
            "К" => "K",
            "Л" => "N",
            "М" => "M",
            "Н" => "H",
            "О" => "O",
            "П" => "P",
            "Р" => "P",
            "С" => "C",
            "Т" => "T",
            "У" => "y",
            "Ф" => "F",
            "Х" => "X",
            "Ц" => "U",
            "Ч" => "Ch",
            "Ш" => "W",
            "Щ" => "W",
            "Ъ" => "",
            "Ы" => "bl",
            "Ь" => "b",
            "Э" => "e",
            "Ю" => "o",
            "Я" => "R",
            "а" => "a",
            "б" => "b",
            "в" => "b",
            "г" => "r",
            "д" => "A",
            "е" => "e",
            "ё" => "е",
            "ж" => "x",
            "з" => "3",
            "и" => "n",
            "й" => "и",
            "к" => "k",
            "л" => "n",
            "м" => "m",
            "н" => "h",
            "о" => "o",
            "п" => "n",
            "р" => "p",
            "с" => "c",
            "т" => "t",
            "у" => "y",
            "ф" => "o",
            "х" => "x",
            "ц" => "u",
            "ч" => "y",
            "ш" => "w",
            "щ" => "w",
            "ъ" => "b",
            "ы" => "u",
            "ь" => "b",
            "э" => "e",
            "ю" => "o",
            "я" => "r",
            "Є" => "e",
            "І" => "I",
            "Ї" => "i",
            "Ґ" => "R",
            "є" => "e",
            "і" => "i",
            "ї" => "i",
            "ґ" => "r",
            "Ȃ" => "A",
            "Ḉ" => "C",
            "Ḗ" => "E",
            "Ḕ" => "E",
            "Ḝ" => "E",
            "Ȇ" => "E",
            "Ḯ" => "I",
            "Ȋ" => "I",
            "Ṍ" => "O",
            "Ṓ" => "O",
            "Ȏ" => "O",
            "ȃ" => "a",
            "ḉ" => "c",
            "ḗ" => "e",
            "ḕ" => "e",
            "ḝ" => "e",
            "ȇ" => "e",
            "ḯ" => "i",
            "ȋ" => "i",
            "ṍ" => "o",
            "ṓ" => "o",
            "ȏ" => "o",
            "C̆" => "C",
            "c̆" => "c",
            "Ǵ" => "G",
            "ǵ" => "g",
            "Ḫ" => "H",
            "ḫ" => "h",
            "Ḱ" => "K",
            "ḱ" => "k",
            "K̆" => "K",
            "k̆" => "k",
            "Ḿ" => "M",
            "ḿ" => "m",
            "M̆" => "M",
            "m̆" => "m",
            "N̆" => "N",
            "n̆" => "n",
            "P̆" => "P",
            "p̆" => "p",
            "R̆" => "R",
            "r̆" => "r",
            "Ȓ" => "R",
            "ȓ" => "r",
            "T̆" => "T",
            "t̆" => "t",
            "Ȗ" => "U",
            "ȗ" => "u",
            "V̆" => "V",
            "v̆" => "v",
            "Ẃ" => "W",
            "ẃ" => "w",
            "X̆" => "X",
            "x̆" => "x",
            "Y̆" => "Y",
            "y̆" => "y",
            "ƒ" => "f",
            "Ṹ" => "U",
            "ṹ" => "u",
            "Ǻ" => "A",
            "ǻ" => "a",
            "Ǽ" => "AE",
            "ǽ" => "ae",
            "Ǿ" => "O",
            "ǿ" => "o",
            "Ṕ" => "P",
            "ṕ" => "p",
            "Ṥ" => "S",
            "ṥ" => "s",
            "X́" => "X",
            "x́" => "x",
            "Ѓ" => "Г",
            "ѓ" => "г",
            "Ќ" => "К",
            "ќ" => "к",
            "A̋" => "A",
            "a̋" => "a",
            "E̋" => "E",
            "e̋" => "e",
            "I̋" => "I",
            "i̋" => "i",
            "Ǹ" => "N",
            "ǹ" => "n",
            "Ṑ" => "O",
            "ṑ" => "o",
            "Ẁ" => "W",
            "ẁ" => "w",
            "Ȁ" => "A",
            "ȁ" => "a",
            "Ȅ" => "E",
            "ȅ" => "e",
            "Ȉ" => "I",
            "ȉ" => "i",
            "Ȍ" => "O",
            "ȍ" => "o",
            "Ȑ" => "R",
            "ȑ" => "r",
            "Ȕ" => "U",
            "ȕ" => "u",
            "B̌" => "B",
            "b̌" => "b",
            "Č̣" => "C",
            "č̣" => "c",
            "Ê̌" => "E",
            "ê̌" => "e",
            "F̌" => "F",
            "f̌" => "f",
            "Ǧ" => "G",
            "ǧ" => "g",
            "Ȟ" => "H",
            "ȟ" => "h",
            "J̌" => "J",
            "ǰ" => "j",
            "Ǩ" => "K",
            "ǩ" => "k",
            "M̌" => "M",
            "m̌" => "m",
            "P̌" => "P",
            "p̌" => "p",
            "Q̌" => "Q",
            "q̌" => "q",
            "Ř̩" => "R",
            "ř̩" => "r",
            "Ṧ" => "S",
            "ṧ" => "s",
            "V̌" => "V",
            "v̌" => "v",
            "W̌" => "W",
            "w̌" => "w",
            "X̌" => "X",
            "x̌" => "x",
            "Y̌" => "Y",
            "y̌" => "y",
            "A̧" => "A",
            "a̧" => "a",
            "B̧" => "B",
            "b̧" => "b",
            "Ḑ" => "D",
            "ḑ" => "d",
            "Ȩ" => "E",
            "ȩ" => "e",
            "Ɛ̧" => "E",
            "ɛ̧" => "e",
            "Ḩ" => "H",
            "ḩ" => "h",
            "I̧" => "I",
            "i̧" => "i",
            "Ɨ̧" => "I",
            "ɨ̧" => "i",
            "M̧" => "M",
            "m̧" => "m",
            "O̧" => "O",
            "o̧" => "o",
            "Q̧" => "Q",
            "q̧" => "q",
            "U̧" => "U",
            "u̧" => "u",
            "X̧" => "X",
            "x̧" => "x",
            "Z̧" => "Z",
            "z̧" => "z"

        );
        
        // Apply rules for specific locale if exists
        if ( empty( $locale ) ) {
            $locale = get_locale();
        }
        if ( str_starts_with( $locale, 'de' ) ) {
            $merged_map['Ä'] = 'Ae';
            $merged_map['ä'] = 'ae';
            $merged_map['Ö'] = 'Oe';
            $merged_map['ö'] = 'oe';
            $merged_map['Ü'] = 'Ue';
            $merged_map['ü'] = 'ue';
            $merged_map['ß'] = 'ss';
        } elseif ( 'da_DK' === $locale ) {
            $merged_map['Æ'] = 'Ae';
            $merged_map['æ'] = 'ae';
            $merged_map['Ø'] = 'Oe';
            $merged_map['ø'] = 'oe';
            $merged_map['Å'] = 'Aa';
            $merged_map['å'] = 'aa';
        } elseif ( 'ca' === $locale ) {
            $merged_map['l·l'] = 'll';
        } elseif ( 'sr_RS' === $locale || 'bs_BA' === $locale ) {
            $merged_map['Đ'] = 'DJ';
            $merged_map['đ'] = 'dj';
        }
        
        // Replace characters according to merged map
        $text = strtr( $text, $merged_map );
    } else {
        // Process if string is not UTF-8 (assume ISO-8859-1)
        $chars = array();
        $chars['in'] = "\x80\x83\x8a\x8e\x9a\x9e"
            . "\x9f\xa2\xa5\xb5\xc0\xc1\xc2"
            . "\xc3\xc4\xc5\xc7\xc8\xc9\xca"
            . "\xcb\xcc\xcd\xce\xcf\xd1\xd2"
            . "\xd3\xd4\xd5\xd6\xd8\xd9\xda"
            . "\xdb\xdc\xdd\xe0\xe1\xe2\xe3"
            . "\xe4\xe5\xe7\xe8\xe9\xea\xeb"
            . "\xec\xed\xee\xef\xf1\xf2\xf3"
            . "\xf4\xf5\xf6\xf8\xf9\xfa\xfb"
            . "\xfc\xfd\xff";
        $chars['out'] = 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy';
        $text = strtr( $text, $chars['in'], $chars['out'] );
        $double_chars = array();
        $double_chars['in'] = array( "\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe" );
        $double_chars['out'] = array( 'OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th' );
        $text = str_replace( $double_chars['in'], $double_chars['out'], $text );
    }
    
    return $text;
}


/**
 * Convert UTF-8 string to slug format (no accents)
 * 
 * @param string $string UTF-8 string to convert
 * @return string String converted to slug format without accents
 */
function url_slug($str, $options = array()) {
	// Handle null or non-string input
	if ($str === null || !is_string($str)) {
		$str = $str === null ? '' : (string)$str;
	}
	
	// Make sure string is in UTF-8 and strip invalid UTF-8 characters
	$str = remove_accents($str);
	
	// Ensure $str is still a string after remove_accents (should not be null)
	if (!is_string($str)) {
		$str = '';
	}
	
	$defaults = array(
		'delimiter' => '-',
		'limit' => null,
		'lowercase' => true,
		'replacements' => array()
	);
	
	// Merge options
	$options = array_merge($defaults, $options);
	
	
	// Make custom replacements
	$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
	// Replace non-alphanumeric characters with our delimiter
	$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
	// Remove duplicate delimiters
	$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
	// Truncate slug to max. characters
	$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
	// Remove delimiter from ends
	$str = trim($str, $options['delimiter']);
	return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
}

function keyword_slug($str, $options = array()){
    $str = url_slug($str, $options);
    return str_replace('-', ' ', $str);
}
