<?php
define( 'GTM4WP_WPFILTER_COMPILE_DATALAYER', 'gtm4wp_compile_datalayer' );
define( 'GTM4WP_WPFILTER_COMPILE_REMARKTING', 'gtm4wp_compile_remarkering' );
define( 'GTM4WP_WPFILTER_GETTHEGTMTAG', 'gtm4wp_get_the_gtm_tag' );

if ( $GLOBALS[ "gtm4wp_options" ][ GTM4WP_OPTION_DATALAYER_NAME ] == "" ) {
	$GLOBALS[ "gtm4wp_datalayer_name" ] = "dataLayer";
} else {
	$GLOBALS[ "gtm4wp_datalayer_name" ] = $GLOBALS[ "gtm4wp_options" ][ GTM4WP_OPTION_DATALAYER_NAME ];
}

function gtm4wp_is_assoc($arr) {
	// borrowed from
	// http://stackoverflow.com/questions/173400/php-arrays-a-good-way-to-check-if-an-array-is-associative-or-sequential
	return array_keys($arr) !== range(0, count($arr) - 1);
}

if ( !function_exists( "getallheaders") ) { 
	function getallheaders() { 
		$headers = ""; 
		foreach ( $_SERVER as $name => $value ) { 
			if ( substr($name, 0, 5) == "HTTP_" ) { 
				$headers[ str_replace(' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value; 
			} 
		} 
		
		return $headers; 
	} 
}

function gtm4wp_add_basic_datalayer_data( $dataLayer ) {
	global $current_user, $wp_query, $gtm4wp_options;
	
	if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_LOGGEDIN ] ) {
		if ( is_user_logged_in() ) {
			$dataLayer["visitorLoginState"] = "logged-in";
		} else {
			$dataLayer["visitorLoginState"] = "logged-out";
		}
	}
	
	if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_USERROLE ] ) {
		get_currentuserinfo();
		$dataLayer["visitorType"] = ( empty( $current_user->roles[0] ) ? "visitor-logged-out" : $current_user->roles[0] );
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_USERID ] ) {
		$_gtm4wp_userid = get_current_user_id();
		if ( $_gtm4wp_userid > 0 ) {
      $dataLayer["visitorId"] = $_gtm4wp_userid;
    }
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTTITLE ] ) {
		$dataLayer["pageTitle"] = strip_tags( wp_title( "|", false, "right" ) );
	}

	if ( is_singular() ) {
		if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTTYPE ] ) {
			$dataLayer["pagePostType"] = get_post_type();
			$dataLayer["pagePostType2"] = "single-".get_post_type();
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_CATEGORIES ] ) {
			$_post_cats = get_the_category();
			if ( $_post_cats ) {
				$dataLayer["pageCategory"] = array();
				foreach( $_post_cats as $_one_cat ) {
					$dataLayer["pageCategory"][] = $_one_cat->slug;
				}
			}
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_TAGS ] ) {
			$_post_tags = get_the_tags();
			if ( $_post_tags ) {
				$dataLayer["pageAttributes"] = array();
				foreach( $_post_tags as $_one_tag ) {
					$dataLayer["pageAttributes"][] = $_one_tag->slug;
				}
			}
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_AUTHOR ] ) {
			$postuser = get_userdata( $GLOBALS["post"]->post_author );
			if ( false !== $postuser ) {
				$dataLayer["pagePostAuthor"] = $postuser->display_name;
			}
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTDATE ] ) {
			$dataLayer["pagePostDate"] = get_the_date();
			$dataLayer["pagePostDateYear"] = get_the_date( "Y" );
			$dataLayer["pagePostDateMonth"] = get_the_date( "m" );
			$dataLayer["pagePostDateDay"] = get_the_date( "d" );
		}
	}

	if ( is_archive() || is_post_type_archive() ) {
		if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTTYPE ] ) {
			$dataLayer["pagePostType"] = get_post_type();

			if ( is_category() ) {
				$dataLayer["pagePostType2"] = "category-".get_post_type();
			} else if ( is_tag() ) {
				$dataLayer["pagePostType2"] = "tag-".get_post_type();
			} else if ( is_tax() ) {
				$dataLayer["pagePostType2"] = "tax-".get_post_type();
			} else if ( is_author() ) {
				$dataLayer["pagePostType2"] = "author-".get_post_type();
			} else if ( is_year() ) {
				$dataLayer["pagePostType2"] = "year-".get_post_type();

				if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTDATE ] ) {
					$dataLayer["pagePostDateYear"] = get_the_date( "Y" );
				}
			} else if ( is_month() ) {
				$dataLayer["pagePostType2"] = "month-".get_post_type();

				if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTDATE ] ) {
					$dataLayer["pagePostDateYear"] = get_the_date( "Y" );
					$dataLayer["pagePostDateMonth"] = get_the_date( "m" );
				}
			} else if ( is_day() ) {
				$dataLayer["pagePostType2"] = "day-".get_post_type();

				if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTDATE ] ) {
					$dataLayer["pagePostDate"] = get_the_date();
					$dataLayer["pagePostDateYear"] = get_the_date( "Y" );
					$dataLayer["pagePostDateMonth"] = get_the_date( "m" );
					$dataLayer["pagePostDateDay"] = get_the_date( "d" );
				}
			} else if ( is_time() ) {
				$dataLayer["pagePostType2"] = "time-".get_post_type();
			} else if ( is_date() ) {
				$dataLayer["pagePostType2"] = "date-".get_post_type();

				if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTDATE ] ) {
					$dataLayer["pagePostDate"] = get_the_date();
					$dataLayer["pagePostDateYear"] = get_the_date( "Y" );
					$dataLayer["pagePostDateMonth"] = get_the_date( "m" );
					$dataLayer["pagePostDateDay"] = get_the_date( "d" );
				}
			}
		}

		if ( ( is_tax() || is_category() ) && $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_CATEGORIES ] ) {
			$_post_cats = get_the_category();
			$dataLayer["pageCategory"] = array();
			foreach( $_post_cats as $_one_cat ) {
				$dataLayer["pageCategory"][] = $_one_cat->slug;
			}
		}

		if ( ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_AUTHOR ] ) && ( is_author() ) ) {
			$dataLayer["pagePostAuthor"] = get_the_author();
		}
	}
	
	if ( is_search() ) {
		$dataLayer["siteSearchTerm"] = get_search_query();
		$dataLayer["siteSearchFrom"] = $_SERVER["HTTP_REFERER"];
		$dataLayer["siteSearchResults"] = $wp_query->post_count;
	}
	
	if ( is_front_page() && $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTTYPE ] ) {
		$dataLayer["pagePostType"] = "frontpage";
	}
	
	if ( !is_front_page() && is_home() && $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTTYPE ] ) {
		$dataLayer["pagePostType"] = "bloghome";
	}
	
	if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_BROWSERDATA ] || $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_OSDATA ] || $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_DEVICEDATA ] ) {
		require_once( dirname( __FILE__ ) . "/../js/whichbrowser/libraries/whichbrowser.php" );

		$detected = new WhichBrowser( array( "headers" => getallheaders() ) );

		if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_BROWSERDATA ] ) {
			$dataLayer["browserName"]         = isset( $detected->browser->name ) ? $detected->browser->name : "";
			$dataLayer["browserVersion"]      = isset( $detected->browser->version->value ) ? $detected->browser->version->value : "";

			$dataLayer["browserEngineName"]         = isset( $detected->engine->name ) ? $detected->engine->name : "";
			$dataLayer["browserEngineVersion"]      = isset( $detected->engine->version->value ) ? $detected->engine->version->value : "";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_OSDATA ] ) {
			$dataLayer["osName"]         = isset( $detected->os->name ) ? $detected->os->name : "";
			$dataLayer["osVersion"]      = isset( $detected->os->version->value ) ? $detected->os->version->value : "";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_DEVICEDATA ] ) {
			$dataLayer["deviceType"]         = isset( $detected->device->type ) ? $detected->device->type : "";
			$dataLayer["deviceManufacturer"] = isset( $detected->device->manufacturer ) ? $detected->device->manufacturer : "";
			$dataLayer["deviceModel"]        = isset( $detected->device->model ) ? $detected->device->model : "";
		}
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_POSTCOUNT ] ) {
		$dataLayer["postCountOnPage"] = (int) $wp_query->post_count;
		$dataLayer["postCountTotal"]  = (int) $wp_query->found_posts;
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_ENABLE ] > 0 ) {
		$_gtmrestrictlistitems = array();
		
		// IDs from https://developers.google.com/tag-manager/devguide#security
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_ADADVISOR ] ) {
			$_gtmrestrictlistitems[] = "ta";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_ADROLL ] ) {
			$_gtmrestrictlistitems[] = "asp";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_AWCONV ] ) {
			$_gtmrestrictlistitems[] = "awct";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_AWREMARKET ] ) {
			$_gtmrestrictlistitems[] = "sp";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_BIZO ] ) {
			$_gtmrestrictlistitems[] = "bzi";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_CLICKTALE ] ) {
			$_gtmrestrictlistitems[] = "cts";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_COMSCORE ] ) {
			$_gtmrestrictlistitems[] = "csm";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_CUSTOMHTML ] ) {
			$_gtmrestrictlistitems[] = "html";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_CUSTOMIMG ] ) {
			$_gtmrestrictlistitems[] = "img";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_DBLCLKCOUNT ] ) {
			$_gtmrestrictlistitems[] = "flc";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_DBLCLKSALES ] ) {
			$_gtmrestrictlistitems[] = "fls";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_GACLASSIC ] ) {
			$_gtmrestrictlistitems[] = "ga";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MARIN ] ) {
			$_gtmrestrictlistitems[] = "ms";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MPLEXIFRAME ] ) {
			$_gtmrestrictlistitems[] = "mpm";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MPLEXROI ] ) {
			$_gtmrestrictlistitems[] = "mpr";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MEDIA6DEG ] ) {
			$_gtmrestrictlistitems[] = "m6d";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_TURNCONV ] ) {
			$_gtmrestrictlistitems[] = "tc";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_TURNDATA ] ) {
			$_gtmrestrictlistitems[] = "tdc";
		}
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_UA ] ) {
			$_gtmrestrictlistitems[] = "ua";
		}
		
		$_gtmwhitelist = array();
		$_gtmblacklist = array();
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_ENABLE ] == 1 ) {
			$_gtmblacklist = array_merge($_gtmblacklist, $_gtmrestrictlistitems);
		} else {
			$_gtmwhitelist = array_merge($_gtmwhitelist, $_gtmrestrictlistitems);
		}
		
		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_DOMELEMENT ] ) {
			$_gtmwhitelist[] = "d";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_CUSTOMJS ] ) {
			$_gtmwhitelist[] = "jsm";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_CONSTANT ] ) {
			$_gtmwhitelist[] = "c";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_1STCOOKIE ] ) {
			$_gtmwhitelist[] = "k";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_EVENTNAME ] ) {
			$_gtmwhitelist[] = "e";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_JSVAR ] ) {
			$_gtmwhitelist[] = "j";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_DLAYERVAR ] ) {
			$_gtmwhitelist[] = "v";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_RANDOMNUM ] ) {
			$_gtmwhitelist[] = "r";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_REFERRER ] ) {
			$_gtmwhitelist[] = "f";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_URL ] ) {
			$_gtmwhitelist[] = "u";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_BLACKLIST_MACRO_AUTOEVENT ] ) {
			$_gtmwhitelist[] = "v";
		}

		$dataLayer[ "gtm.whitelist" ] = $_gtmwhitelist;
		$dataLayer[ "gtm.blacklist" ] = $_gtmblacklist;
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_WEATHER ] ) {
		$dataLayer[ "weatherCategory" ] = __( "(no weather data available)", GTM4WP_TEXTDOMAIN );
		$dataLayer[ "weatherDescription" ] = __( "(no weather data available)", GTM4WP_TEXTDOMAIN );
		$dataLayer[ "weatherTemp" ] = 0;
		$dataLayer[ "weatherPressure" ] = 0;
		$dataLayer[ "weatherWindSpeed" ] = 0;
		$dataLayer[ "weatherWindDeg" ] = 0;

		$gtm4wp_sessionid = array_key_exists( "gtm4wp_sessoionid", $_COOKIE ) ? $_COOKIE[ "gtm4wp_sessoionid" ] : "";
		// this is needed so that nobody can do a hack by editing our cookie
		$gtm4wp_sessionid = str_replace( "'", "", trim( basename( $gtm4wp_sessionid ) ) );

		if ( "" !== $gtm4wp_sessionid ) {
			$weatherdata = get_transient( 'gtm4wp-weatherdata-'.$gtm4wp_sessionid );

			if ( false !== $weatherdata ) {
				$dataLayer[ "weatherCategory" ] = $weatherdata->weather[0]->main;
				$dataLayer[ "weatherDescription" ] = $weatherdata->weather[0]->description;
				$dataLayer[ "weatherTemp" ] = $weatherdata->main->temp;
				$dataLayer[ "weatherPressure" ] = $weatherdata->main->pressure;
				$dataLayer[ "weatherWindSpeed" ] = $weatherdata->wind->speed;
				$dataLayer[ "weatherWindDeg" ] = $weatherdata->wind->deg;
			}
		}
	}
	
	return $dataLayer;
}

function gtm4wp_wp_loaded() {
        global $gtm4wp_options;

	if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_WEATHER ] ) {
		$gtm4wp_sessionid = array_key_exists( "gtm4wp_sessoionid", $_COOKIE ) ? $_COOKIE[ "gtm4wp_sessoionid" ] : "";
		// this is needed so that nobody can do a hack by editing our cookie
		$gtm4wp_sessionid = str_replace( "'", "", trim( basename( $gtm4wp_sessionid ) ) );

		if ( "" === $gtm4wp_sessionid ) {
			$gtm4wp_sessionid = substr( md5( date("Ymd_His").rand() ), 0, 20 );
			setcookie( "gtm4wp_sessoionid", $gtm4wp_sessionid, time()+(60*60*24*365*2) );
		}

		$weatherdata = get_transient( 'gtm4wp-weatherdata-'.$gtm4wp_sessionid );

		if ( false === $weatherdata ) {
			$gtm4wp_geodata = @file_get_contents('http://www.geoplugin.net/php.gp?ip='.$_SERVER['REMOTE_ADDR']);
			if ( $gtm4wp_geodata ) {
				$gtm4wp_geodata = unserialize( $gtm4wp_geodata );
				if ( array_key_exists( 'geoplugin_latitude', $gtm4wp_geodata ) && array_key_exists( 'geoplugin_longitude', $gtm4wp_geodata ) ) {
					$weatherdata = @file_get_contents('http://api.openweathermap.org/data/2.5/weather?lat=' . $gtm4wp_geodata[ "geoplugin_latitude" ] . '&lon=' . $gtm4wp_geodata[ "geoplugin_longitude" ] . '&units=' . ($gtm4wp_options[ GTM4WP_OPTION_INCLUDE_WEATHERUNITS ] == 0 ? 'metric' : 'imperial'));
					if ( $weatherdata ) {
						$weatherdata = @json_decode( $weatherdata );
						if ( is_object( $weatherdata ) ) {
							set_transient( 'gtm4wp-weatherdata-'.$gtm4wp_sessionid, $weatherdata, 60 * 60 );
						}
					}
				}
			}
		}
	}
}

function gtm4wp_get_the_gtm_tag() {
	global $gtm4wp_options, $gtm4wp_datalayer_name;
	
	$_gtm_tag = '';
	
	if ( $gtm4wp_options[ GTM4WP_OPTION_GTM_CODE ] != "" ) {
		$_gtm_tag .= '
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=' . $gtm4wp_options[ GTM4WP_OPTION_GTM_CODE ] . '"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
\'//www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,\'script\',\'' . $gtm4wp_datalayer_name . '\',\'' . $gtm4wp_options[ GTM4WP_OPTION_GTM_CODE ] . '\');</script>
<!-- End Google Tag Manager -->';
	}

	return apply_filters( GTM4WP_WPFILTER_GETTHEGTMTAG, $_gtm_tag );
}

function gtm4wp_the_gtm_tag() {
	echo gtm4wp_get_the_gtm_tag();
}

function gtm4wp_enqueue_scripts() {
	global $gtm4wp_options, $gtp4wp_plugin_url;
		
	if ( $gtm4wp_options[ GTM4WP_OPTION_EVENTS_OUTBOUND ] ) {
		wp_enqueue_script( "gtm4wp-outbound-click-tracker", $gtp4wp_plugin_url . "js/gtm4wp-outbound-click-tracker.js", array( "jquery" ), "1.0", false );
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_EVENTS_DOWNLOADS ] ) {
		wp_enqueue_script( "gtm4wp-download-tracker", $gtp4wp_plugin_url . "js/gtm4wp-download-tracker.js", array( "jquery" ), "1.0", false );
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_EVENTS_EMAILCLICKS ] ) {
		wp_enqueue_script( "gtm4wp-email-link-tracker", $gtp4wp_plugin_url . "js/gtm4wp-email-link-tracker.js", array( "jquery" ), "1.0", false );
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_INTEGRATE_WPCF7 ] ) {
		wp_enqueue_script( "gtm4wp-contact-form-7-tracker", $gtp4wp_plugin_url . "js/gtm4wp-contact-form-7-tracker.js", array( "jquery" ), "1.0", false );
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_EVENTS_FORMMOVE ] ) {
		wp_enqueue_script( "gtm4wp-form-move-tracker", $gtp4wp_plugin_url . "js/gtm4wp-form-move-tracker.js", array( "jquery" ), "1.0", false );
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_EVENTS_SOCIAL ] ) {
		wp_enqueue_script( "gtm4wp-social-actions", $gtp4wp_plugin_url . "js/gtm4wp-social-tracker.js", array( "jquery" ), "1.0", false );
	}

	if ( isset ( $GLOBALS["woocommerce"] ) ) {
		require_once( dirname( __FILE__ ) . "/../integration/woocommerce.php" );
	}

	if ( $gtm4wp_options[ GTM4WP_OPTION_SCROLLER_ENABLED ] ) {
		wp_enqueue_script( "gtm4wp-scroll-tracking", $gtp4wp_plugin_url . "js/analytics-talk-content-tracking.js", array( "jquery" ), "1.0", false );
	}
}

function gtm4wp_wp_footer() {
	global $gtm4wp_options;

	if ( GTM4WP_PLACEMENT_FOOTER == $gtm4wp_options[ GTM4WP_OPTION_GTM_PLACEMENT ] ) {
		gtm4wp_the_gtm_tag();
	}
}

function gtm4wp_wp_body_open() {
	global $gtm4wp_options;

	if ( GTM4WP_PLACEMENT_BODYOPEN == $gtm4wp_options[ GTM4WP_OPTION_GTM_PLACEMENT ] ) {
		gtm4wp_the_gtm_tag();
	}
}

function gtm4wp_wp_header_begin() {
	global $gtm4wp_datalayer_name, $gtm4wp_options;

	$_gtm_header_content = '
<!-- Google Tag Manager for WordPress by DuracellTomi - http://duracelltomi.com -->
<script type="text/javascript">
	var gtm4wp_datalayer_name = "' . $gtm4wp_datalayer_name . '";
	var ' . $gtm4wp_datalayer_name . ' = ' . $gtm4wp_datalayer_name . ' || []';
	
	if ( $gtm4wp_options[ GTM4WP_OPTION_SCROLLER_ENABLED ] ) {
		$_gtm_header_content .= '

	var gtm4wp_scrollerscript_debugmode         = ' . ( $gtm4wp_options[ GTM4WP_OPTION_SCROLLER_DEBUGMODE ] ? 'true' : 'false' ) . ';
	var gtm4wp_scrollerscript_callbacktime      = ' . (int) $gtm4wp_options[ GTM4WP_OPTION_SCROLLER_CALLBACKTIME ] . ';
	var gtm4wp_scrollerscript_readerlocation    = ' . (int) $gtm4wp_options[ GTM4WP_OPTION_SCROLLER_DISTANCE ] . ';
	var gtm4wp_scrollerscript_contentelementid  = "' . $gtm4wp_options[ GTM4WP_OPTION_SCROLLER_CONTENTID ] . '";
	var gtm4wp_scrollerscript_scannertime       = ' . (int) $gtm4wp_options[ GTM4WP_OPTION_SCROLLER_READERTIME ] . ';';
	}

	$_gtm_header_content .= '
</script>
<!-- End Google Tag Manager for WordPress by DuracellTomi -->';

	echo $_gtm_header_content;
}

function gtm4wp_wp_header_end() {
	global $gtm4wp_datalayer_name, $gtm4wp_options;

	$_gtm_tag = '';

	if ( $gtm4wp_options[ GTM4WP_OPTION_GTM_CODE ] != "" ) {
		$_gtm_tag .= '
<!-- Google Tag Manager for WordPress by DuracellTomi -->
<script type="text/javascript">';

		$gtm4wp_datalayer_data = array();
		$gtm4wp_datalayer_data = (array) apply_filters( GTM4WP_WPFILTER_COMPILE_DATALAYER, $gtm4wp_datalayer_data );
		
		if ( $gtm4wp_options[ GTM4WP_OPTION_INCLUDE_REMARKETING ] ) {
			// add adwords remarketing tags as suggested here:
			// https://support.google.com/tagmanager/answer/3002580?hl=en

			$gtm4wp_remarketing_tags = (array) apply_filters( GTM4WP_WPFILTER_COMPILE_REMARKTING, $gtm4wp_datalayer_data );

			$_gtm_tag .= '
	var google_tag_params = ' . json_encode( $gtm4wp_remarketing_tags ) . ';';
			$gtm4wp_datalayer_data["google_tag_params"] = "-~-window.google_tag_params-~-";
		}

		if ( $gtm4wp_options[ GTM4WP_OPTION_EVENTS_DOWNLOADS ] ) {
			$_gtm_tag .= '
	jQuery( function() {
		gtm4wp_track_downloads( "' . str_replace( '"', '', $gtm4wp_options[ GTM4WP_OPTION_EVENTS_DWLEXT ] ) . '" );
	});';
		}
		
		$_gtm_tag .= '
	' . $gtm4wp_datalayer_name . '.push(' . str_replace(
			array( '"-~-', '-~-"' ),
			array( "", "" ),
			json_encode( $gtm4wp_datalayer_data )
		) . ');';

		$_gtm_tag .= '
</script>';
	}

	echo $_gtm_tag;	
}

add_action( "wp_enqueue_scripts", "gtm4wp_enqueue_scripts" );
add_action( "wp_head", "gtm4wp_wp_header_begin", 1 );
add_action( "wp_head", "gtm4wp_wp_header_end", 100 );
add_action( "wp_footer", "gtm4wp_wp_footer" );
add_action( "wp_loaded", "gtm4wp_wp_loaded" );
add_filter( GTM4WP_WPFILTER_COMPILE_DATALAYER, "gtm4wp_add_basic_datalayer_data" );

// to be able to easily migrate from other Google Tag Manager plugins
add_action( "body_open", "gtm4wp_wp_body_open" );
