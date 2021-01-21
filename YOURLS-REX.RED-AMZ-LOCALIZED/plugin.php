<?php
/*
Plugin Name: YOURLS-REX.RED-AMZ-LOCALIZED
Plugin URI: https://rex.red/?utm_source=amzlocalized-github-plugin&utm_medium=github-amzlocalized-plugin
Description: YOURLS-REX.RED-AMZ-LOCALIZED When adding a shortURL prefix it with <<amz->> to create all amazon local links automatically.
Version: 0.3
Author: Dragos Ion 
Author URI: https://diy.rednumberone.com/?utm_source=amzlocalized-github-plugin&utm_medium=github-amzlocalized-plugin
License: GPL 2.0
*/

// No direct call
if ( !defined( 'YOURLS_ABSPATH' ) )die();

/* Example of an action
 *
 * We're going to add an entry to the menu.
 *
 * The menu is drawn by function yourls_html_menu() in file includes/functions-html.php.
 * Right before the function outputs the closing </ul>, notice the following function call:
 * yourls_do_action( 'admin_menu' );
 * This function says: "hey, for your information, I've just done something called 'admin menu', thought I'd let you know..."
 *
 * We're going to hook into this action and add our menu entry
 */

yourls_add_action( 'admin_menu', 'dirno_sample_add_menu' );
/* This says: when YOURLS does action 'admin_menu', call function 'ozh_sample_add_menu'
 */

function dirno_sample_add_menu() {
  echo '<li><a href="#">AMZ-LOCALIZED-USE: amz- prefix</a></li>';
}
/* And that's it. Activate the plugin and notice the new menu entry.
 */


// starts here


yourls_add_filter( 'get_shorturl_charset', 'diro_plus_in_charset' );

function diro_plus_in_charset( $in ) {
  return $in . '+';
}

yourls_add_filter( 'custom_keyword', 'dirorex_yourls_custom_keyword' );

function dirorex_yourls_custom_keyword( $keyword, $url, $title ) {
  $keyword = replace_plus_with_minus( $keyword );
  //print $keyword;
  //die;
  return $keyword;

}

yourls_add_action( 'post_add_new_link', 'urlMakeLocalized' );

function urlMakeLocalized( $linkArray ) {
  $urlLong = $linkArray[ 0 ]; // 0 = full url, 1 = short url, 2 = empty (keyword) - use the url as title too.
  $urlShort = $linkArray[ 1 ];
  $urlTitle = $linkArray[ 0 ];

  $amzPrefixTag = "amz-";


  $urlShortPrefix = '';
  $isAmzMakeURL = false;
  $urlShort_AMZInitial = 'zz';

  $amzdom_array = array(
    array(
      'country_code' => 'us',
      'domain_replace_str' => 'amazon.com/'
    ),
    array(
      'country_code' => 'gb',
      'domain_replace_str' => 'amazon.co.uk/'
    ),
    array(
      'country_code' => 'de',
      'domain_replace_str' => 'amazon.de/'
    ),
    array(
      'country_code' => 'it',
      'domain_replace_str' => 'amazon.it/'
    ),
    array(
      'country_code' => 'fr',
      'domain_replace_str' => 'amazon.fr/'
    ),
    array(
      'country_code' => 'se',
      'domain_replace_str' => 'amazon.se/'
    ),
    array(
      'country_code' => 'sa',
      'domain_replace_str' => 'amazon.sa/'
    ),
    array(
      'country_code' => 'mx',
      'domain_replace_str' => 'amazon.com.mx/'
    ),
    array(
      'country_code' => 'br',
      'domain_replace_str' => 'amazon.com.br/'
    ),
    array(
      'country_code' => 'es',
      'domain_replace_str' => 'amazon.es/'
    ),
    array(
      'country_code' => 'nl',
      'domain_replace_str' => 'amazon.nl/'
    ),
    array(
      'country_code' => 'jp',
      'domain_replace_str' => 'amazon.jp/'
    ),
    array(
      'country_code' => 'sg',
      'domain_replace_str' => 'amazon.sg/'
    ),
    array(
      'country_code' => 'au',
      'domain_replace_str' => 'amazon.com.au/'
    ),
    array(
      'country_code' => 'ca',
      'domain_replace_str' => 'amazon.ca/'
    )
  );

  $matchTagExpression = "/^" . $amzPrefixTag . "/";
  if ( preg_match( $matchTagExpression, $urlShort ) ) {
    $isAmzMakeURL = true;
    $urlShortPrefix = $amzPrefixTag;
    $urlMainDomainReplaceString = 'amazon.com/';
    $urlShort_AMZInitial = $urlShort;
  }

  if ( $isAmzMakeURL ) {
    //remove the prefix, it is useful only for us inside this function to know this URL is to be amz localized.
    $urlShort = remove_prefix( $urlShort, $urlShortPrefix );
    $urlLong_Localized = '';
    $urlShort_Localized = '';


    //expecting initial URL to be amazon.com
    foreach ( $amzdom_array as $row => $amzdom_single_array ) {
      $urlAmzLocalized_CountryCode = $amzdom_single_array[ 'country_code' ];
      $urlAmzLocalized_ReplaceString = $amzdom_single_array[ 'domain_replace_str' ];

      $urlLong_Localized = str_replace( $urlMainDomainReplaceString, $urlAmzLocalized_ReplaceString, $urlLong );
      $urlShort_Localized = $urlShort . '-' . $urlAmzLocalized_CountryCode;

      $newurl_Localized = yourls_add_new_link( $urlLong_Localized, $urlShort_Localized, $urlLong_Localized );

      //echo $urlLong_Localized;
      //echo $urlShort_Localized;
      // echo 'done';
    }

    //Add this at the end so that the latest is without country code - to copy and share...
    sleep( 1 ); //workaround no key/id in db - using timestamp+
    $newurl = yourls_add_new_link( $urlLong, $urlShort, $urlLong ); //add this to have a version of this without the prefix. 	

    //Remove the link that has the prefix, not ideal but it better work... future me, don't blame me for this.
    if ( strlen( $urlShort_AMZInitial ) > 3 ) {
      $query = yourls_delete_link_by_keyword( $urlShort_AMZInitial );

    }
  } // end isAmzMakeURL


} //end urlMakeLocalized

function remove_prefix( $text, $prefix ) {
  //return ltrim($text, $prefix);
  return str_replace( $prefix, '', $text ); //Replacing + with - makes it easier to copy amazon url keywords into short url
}

function replace_plus_with_minus( $text ) {
  return str_replace( ' ', '-', str_replace( '+', '-', $text ) ); //Replacing + with - makes it easier to copy amazon url keywords into short url

}