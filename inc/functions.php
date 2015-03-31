<?php

// RFC 3986 delimeter splitting implementation.
function txwt_decompose_url( $url ) {
	$result = array(
		"scheme" => "",
		"authority" => "",
		"login" => "",
		"loginusername" => "",
		"loginpassword" => "",
		"host" => "",
		"port" => "",
		"path" => "",
		"query" => "",
		"queryvars" => array( ),
		"fragment" => ""
	);

	$url = str_replace( "&amp;", "&", $url );

	$pos = strpos( $url, "#" );
	if ( $pos !== false ) {
		$result[ "fragment" ] = substr( $url, $pos + 1 );
		$url = substr( $url, 0, $pos );
	}

	$pos = strpos( $url, "?" );
	if ( $pos !== false ) {
		$result[ "query" ] = str_replace( " ", "+", substr( $url, $pos + 1 ) );
		$url = substr( $url, 0, $pos );
		$vars = explode( "&", $result[ "query" ] );
		foreach ( $vars as $var ) {
			$pos = strpos( $var, "=" );
			if ( $pos === false ) {
				$name = $var;
				$value = "";
			} else {
				$name = substr( $var, 0, $pos );
				$value = substr( $var, $pos + 1 );
			}
			if ( !isset( $result[ "queryvars" ][ urldecode( $name ) ] ) )
				$result[ "queryvars" ][ urldecode( $name ) ] = array( );
			$result[ "queryvars" ][ urldecode( $name ) ][ ] = urldecode( $value );
		}
	}

	$url = str_replace( "\\", "/", $url );

	$pos = strpos( $url, ":" );
	$pos2 = strpos( $url, "/" );
	if ( $pos !== false && ($pos2 === false || $pos < $pos2) ) {
		$result[ "scheme" ] = strtolower( substr( $url, 0, $pos ) );
		$url = substr( $url, $pos + 1 );
	}

	if ( substr( $url, 0, 2 ) != "//" )
		$result[ "path" ] = $url;
	else {
		$url = substr( $url, 2 );
		$pos = strpos( $url, "/" );
		if ( $pos !== false ) {
			$result[ "path" ] = substr( $url, $pos );
			$url = substr( $url, 0, $pos );
		}
		$result[ "authority" ] = $url;

		$pos = strpos( $url, "@" );
		if ( $pos !== false ) {
			$result[ "login" ] = substr( $url, 0, $pos );
			$url = substr( $url, $pos + 1 );
			$pos = strpos( $result[ "login" ], ":" );
			if ( $pos === false )
				$result[ "loginusername" ] = urldecode( $result[ "login" ] );
			else {
				$result[ "loginusername" ] = urldecode( substr( $result[ "login" ], 0, $pos ) );
				$result[ "loginpassword" ] = urldecode( substr( $result[ "login" ], $pos + 1 ) );
			}
		}

		$pos = strpos( $url, "]" );
		if ( substr( $url, 0, 1 ) == "[" && $pos !== false ) {
			// IPv6 literal address.
			$result[ "host" ] = substr( $url, 0, $pos + 1 );
			$url = substr( $url, $pos + 1 );

			$pos = strpos( $url, ":" );
			if ( $pos !== false ) {
				$result[ "port" ] = substr( $url, $pos + 1 );
				$url = substr( $url, 0, $pos );
			}
		} else {
			// Normal host[:port].
			$pos = strpos( $url, ":" );
			if ( $pos !== false ) {
				$result[ "port" ] = substr( $url, $pos + 1 );
				$url = substr( $url, 0, $pos );
			}

			$result[ "host" ] = $url;
		}
	}

	return $result;
}

// Takes a txwt_decompose_url() array and condenses it into a string.
function txwt_regenerate_url( $data ) {
	$result = "";
	if ( isset( $data[ "host" ] ) && $data[ "host" ] != "" ) {
		if ( isset( $data[ "scheme" ] ) && $data[ "scheme" ] != "" )
			$result = $data[ "scheme" ] . "://";
		if ( isset( $data[ "loginusername" ] ) && $data[ "loginusername" ] != "" && isset( $data[ "loginpassword" ] ) )
			$result .= rawurlencode( $data[ "loginusername" ] ) . ($data[ "loginpassword" ] != "" ? ":" . rawurlencode( $data[ "loginpassword" ] ) : "") . "@";
		else if ( isset( $data[ "login" ] ) && $data[ "login" ] != "" )
			$result .= $data[ "login" ] . "@";

		$result .= $data[ "host" ];
		if ( isset( $data[ "port" ] ) && $data[ "port" ] != "" )
			$result .= ":" . $data[ "port" ];

		if ( isset( $data[ "path" ] ) ) {
			$data[ "path" ] = str_replace( "\\", "/", $data[ "path" ] );
			if ( substr( $data[ "path" ], 0, 1 ) != "/" )
				$data[ "path" ] = "/" . $data[ "path" ];
			$result .= $data[ "path" ];
		}
	}
	else if ( isset( $data[ "authority" ] ) && $data[ "authority" ] != "" ) {
		if ( isset( $data[ "scheme" ] ) && $data[ "scheme" ] != "" )
			$result = $data[ "scheme" ] . "://";

		$result .= $data[ "authority" ];

		if ( isset( $data[ "path" ] ) ) {
			$data[ "path" ] = str_replace( "\\", "/", $data[ "path" ] );
			if ( substr( $data[ "path" ], 0, 1 ) != "/" )
				$data[ "path" ] = "/" . $data[ "path" ];
			$result .= $data[ "path" ];
		}
	}
	else if ( isset( $data[ "path" ] ) ) {
		if ( isset( $data[ "scheme" ] ) && $data[ "scheme" ] != "" )
			$result = $data[ "scheme" ] . ":";

		$result .= $data[ "path" ];
	}

	if ( isset( $data[ "query" ] ) ) {
		if ( $data[ "query" ] != "" )
			$result .= "?" . $data[ "query" ];
	}
	else if ( isset( $data[ "queryvars" ] ) ) {
		$data[ "query" ] = array( );
		foreach ( $data[ "queryvars" ] as $key => $vals ) {
			if ( is_string( $vals ) )
				$vals = array( $vals );
			foreach ( $vals as $val )
				$data[ "query" ][ ] = urlencode( $key ) . "=" . urlencode( $val );
		}
		$data[ "query" ] = implode( "&", $data[ "query" ] );

		if ( $data[ "query" ] != "" )
			$result .= "?" . $data[ "query" ];
	}

	if ( isset( $data[ "fragment" ] ) && $data[ "fragment" ] != "" )
		$result .= "#" . $data[ "fragment" ];

	return $result;
}

function txwt_register_switcher_types( $types ) {
	foreach ( $types as $type => $description ) {
		if ( preg_match( '/[^a-z_\-0-9]/i', $type ) ) {
			return false;
		}
	}
	//update_option('txwt_ls_types',$types);
	global $txwt_ls_types;
	$txwt_ls_types = $types;
}

function txwt_get_languages() {
	return $GLOBALS[ 'TXWT' ]->settings[ 'langs' ];
}

function txwt_order_langs( $order, $langs ) {
	if ( is_array( $order ) && !empty( $order ) ) {
		$ordered = array( );
		for ( $i = 0; $i < count( $order ); $i++ ) {
			if ( isset( $langs[ $order[ $i ] ] ) ) {
				$ordered[ $order[ $i ] ] = $langs[ $order[ $i ] ];
				unset( $langs[ $order[ $i ] ] );
			}
		}
		return array_merge( $ordered, $langs );
	} else {
		return $langs;
	}
}

function txwt_get_flag( $lang_code ) {
	$size = 'width="16" height="11"';
	$alt = $lang_code;
	$ext = $GLOBALS[ 'TXWT' ]->settings[ 'lang_switcher' ][ 'custom_flag_ext' ];
	$flag = '<img src="' . txwt_flags_dir() . $lang_code . '.' . $ext . '" ' . $size . ' alt="' . $alt . '" />';
	do_action( 'zwt_get_lang_code_flag', $lang_code, $flag );
	return apply_filters( 'zwt_get_flag', $flag, $lang_code, $size, $alt );
}

function txwt_get_flag_url( $lang_code ) {
	$flag_url = txwt_flags_dir() . $lang_code . '.png';
	do_action( 'txwt_get_flag_url', $lang_code, $flag_url );
	return apply_filters( 'txwt_get_flag_url', $flag_url, $lang_code );
}

function txwt_flags_dir() {
	$ls_settings = $GLOBALS[ 'TXWT' ]->settings[ 'lang_switcher' ];
	if ( $ls_settings[ 'use_custom_flags' ] ) {
		return content_url() . $ls_settings[ 'custom_flag_url' ];
	} else {
		return TXWT_URL . '/images/flags/';
	}
}

function txwt_merge_atts( $defaults, $settings ) {

	$settings = (array) $settings;

	foreach ( $defaults as $default_group => $dg_stgs ) {
		if ( isset( $settings[ $default_group ] ) ) {
			if ( is_array( $dg_stgs ) ) {
				foreach ( $dg_stgs as $dg_key => $dg_val ) {
					if ( !isset( $settings[ $default_group ][ $dg_key ] ) ) {
						$settings[ $default_group ][ $dg_key ] = $dg_val;
					}
				}
			}
		} else {
			$settings[ $default_group ] = $dg_stgs;
		}
	}

	return $settings;
}

/**
 * Returns the language for a language code.
 *
 * @since WordPress 3.0.0
 *
 * @param string $code Optional. The two-letter language code. Default empty.
 * @return string The language corresponding to $code if it exists. If it does not exist,
 *                then the first two letters of $code is returned.
 */
function txwt_format_code_lang( $code = '' ) {
	$code = strtolower( substr( $code, 0, 2 ) );
	$lang_codes = array(
		'aa' => 'Afar', 'ab' => 'Abkhazian', 'af' => 'Afrikaans', 'ak' => 'Akan', 'sq' => 'Albanian', 'am' => 'Amharic', 'ar' => 'Arabic', 'an' => 'Aragonese', 'hy' => 'Armenian', 'as' => 'Assamese', 'av' => 'Avaric', 'ae' => 'Avestan', 'ay' => 'Aymara', 'az' => 'Azerbaijani', 'ba' => 'Bashkir', 'bm' => 'Bambara', 'eu' => 'Basque', 'be' => 'Belarusian', 'bn' => 'Bengali',
		'bh' => 'Bihari', 'bi' => 'Bislama', 'bs' => 'Bosnian', 'br' => 'Breton', 'bg' => 'Bulgarian', 'my' => 'Burmese', 'ca' => 'Catalan; Valencian', 'ch' => 'Chamorro', 'ce' => 'Chechen', 'zh' => 'Chinese', 'cu' => 'Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic', 'cv' => 'Chuvash', 'kw' => 'Cornish', 'co' => 'Corsican', 'cr' => 'Cree',
		'cs' => 'Czech', 'da' => 'Danish', 'dv' => 'Divehi; Dhivehi; Maldivian', 'nl' => 'Dutch; Flemish', 'dz' => 'Dzongkha', 'en' => 'English', 'eo' => 'Esperanto', 'et' => 'Estonian', 'ee' => 'Ewe', 'fo' => 'Faroese', 'fj' => 'Fijjian', 'fi' => 'Finnish', 'fr' => 'French', 'fy' => 'Western Frisian', 'ff' => 'Fulah', 'ka' => 'Georgian', 'de' => 'German', 'gd' => 'Gaelic; Scottish Gaelic',
		'ga' => 'Irish', 'gl' => 'Galician', 'gv' => 'Manx', 'el' => 'Greek, Modern', 'gn' => 'Guarani', 'gu' => 'Gujarati', 'ht' => 'Haitian; Haitian Creole', 'ha' => 'Hausa', 'he' => 'Hebrew', 'hz' => 'Herero', 'hi' => 'Hindi', 'ho' => 'Hiri Motu', 'hu' => 'Hungarian', 'ig' => 'Igbo', 'is' => 'Icelandic', 'io' => 'Ido', 'ii' => 'Sichuan Yi', 'iu' => 'Inuktitut', 'ie' => 'Interlingue',
		'ia' => 'Interlingua (International Auxiliary Language Association)', 'id' => 'Indonesian', 'ik' => 'Inupiaq', 'it' => 'Italian', 'jv' => 'Javanese', 'ja' => 'Japanese', 'kl' => 'Kalaallisut; Greenlandic', 'kn' => 'Kannada', 'ks' => 'Kashmiri', 'kr' => 'Kanuri', 'kk' => 'Kazakh', 'km' => 'Central Khmer', 'ki' => 'Kikuyu; Gikuyu', 'rw' => 'Kinyarwanda', 'ky' => 'Kirghiz; Kyrgyz',
		'kv' => 'Komi', 'kg' => 'Kongo', 'ko' => 'Korean', 'kj' => 'Kuanyama; Kwanyama', 'ku' => 'Kurdish', 'lo' => 'Lao', 'la' => 'Latin', 'lv' => 'Latvian', 'li' => 'Limburgan; Limburger; Limburgish', 'ln' => 'Lingala', 'lt' => 'Lithuanian', 'lb' => 'Luxembourgish; Letzeburgesch', 'lu' => 'Luba-Katanga', 'lg' => 'Ganda', 'mk' => 'Macedonian', 'mh' => 'Marshallese', 'ml' => 'Malayalam',
		'mi' => 'Maori', 'mr' => 'Marathi', 'ms' => 'Malay', 'mg' => 'Malagasy', 'mt' => 'Maltese', 'mo' => 'Moldavian', 'mn' => 'Mongolian', 'na' => 'Nauru', 'nv' => 'Navajo; Navaho', 'nr' => 'Ndebele, South; South Ndebele', 'nd' => 'Ndebele, North; North Ndebele', 'ng' => 'Ndonga', 'ne' => 'Nepali', 'nn' => 'Norwegian Nynorsk; Nynorsk, Norwegian', 'nb' => 'Bokmål, Norwegian, Norwegian Bokmål',
		'no' => 'Norwegian', 'ny' => 'Chichewa; Chewa; Nyanja', 'oc' => 'Occitan, Provençal', 'oj' => 'Ojibwa', 'or' => 'Oriya', 'om' => 'Oromo', 'os' => 'Ossetian; Ossetic', 'pa' => 'Panjabi; Punjabi', 'fa' => 'Persian', 'pi' => 'Pali', 'pl' => 'Polish', 'pt' => 'Portuguese', 'ps' => 'Pushto', 'qu' => 'Quechua', 'rm' => 'Romansh', 'ro' => 'Romanian', 'rn' => 'Rundi', 'ru' => 'Russian',
		'sg' => 'Sango', 'sa' => 'Sanskrit', 'sr' => 'Serbian', 'hr' => 'Croatian', 'si' => 'Sinhala; Sinhalese', 'sk' => 'Slovak', 'sl' => 'Slovenian', 'se' => 'Northern Sami', 'sm' => 'Samoan', 'sn' => 'Shona', 'sd' => 'Sindhi', 'so' => 'Somali', 'st' => 'Sotho, Southern', 'es' => 'Spanish; Castilian', 'sc' => 'Sardinian', 'ss' => 'Swati', 'su' => 'Sundanese', 'sw' => 'Swahili',
		'sv' => 'Swedish', 'ty' => 'Tahitian', 'ta' => 'Tamil', 'tt' => 'Tatar', 'te' => 'Telugu', 'tg' => 'Tajik', 'tl' => 'Tagalog', 'th' => 'Thai', 'bo' => 'Tibetan', 'ti' => 'Tigrinya', 'to' => 'Tonga (Tonga Islands)', 'tn' => 'Tswana', 'ts' => 'Tsonga', 'tk' => 'Turkmen', 'tr' => 'Turkish', 'tw' => 'Twi', 'ug' => 'Uighur; Uyghur', 'uk' => 'Ukrainian', 'ur' => 'Urdu', 'uz' => 'Uzbek',
		've' => 'Venda', 'vi' => 'Vietnamese', 'vo' => 'Volapük', 'cy' => 'Welsh', 'wa' => 'Walloon', 'wo' => 'Wolof', 'xh' => 'Xhosa', 'yi' => 'Yiddish', 'yo' => 'Yoruba', 'za' => 'Zhuang; Chuang', 'zu' => 'Zulu' );

	/**
	 * Filter the language codes.
	 *
	 * @since MU
	 *
	 * @param array  $lang_codes Key/value pair of language codes where key is the short version.
	 * @param string $code       A two-letter designation of the language.
	 */
	$lang_codes = apply_filters( 'lang_codes', $lang_codes, $code );
	return strtr( $code, $lang_codes );
}

function txwt_ls_seperator( $sep ) {
	$sep_html = '';
	if ( !empty( $sep ) ) {
		$sep_html = '<span class="txwt-ls_sep">' . $sep . '</span>';
	}
	return apply_filters( 'txwt_ls_seperator', $sep_html );
}

function txwt_allow_subdomain_install() {
	$domain = preg_replace( '|https?://([^/]+)|', '$1', get_option( 'home' ) );
	if ( parse_url( get_option( 'home' ), PHP_URL_PATH ) || 'localhost' == $domain || preg_match( '|^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$|', $domain ) )
		return false;

	return true;
}