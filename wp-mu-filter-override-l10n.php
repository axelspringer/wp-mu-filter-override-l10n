<?php

// @codingStandardsIgnoreFile

/**
 * Cache translation
 *
 * @param boolean $override
 * @param string $domain
 * @param string $mofile
 *
 * @return boolean
 */
function custom_override_load_textdomain( $override, $domain, $mofile ) {

    global $l10n;


    // check if $mofile exisiste and is readable
    if ( ! ( is_file( $mofile ) && is_readable( $mofile ) ) ) {
        return false;
    }

    // creates a unique key for cache
    $key = md5( $mofile );

    // I try to retrive data from cache
    $data = wp_cache_get( $key, $domain );

    // Retrieve the last modified date of the translation files
    $mtime = filemtime( $mofile );

    $mo = new \MO();

    // if cache not return data or data it's old
    if ( ! $data || ! isset( $data['mtime'] ) || ( $mtime > $data['mtime'] ) ) {
        // retrive data from MO file
        if ( $mo->import_from_file( $mofile ) ) {

            $data = array(
                'mtime'   => $mtime,
                'entries' => $mo->entries,
                'headers' => $mo->headers
            );

            // save data in cache
            wp_cache_set( $key, $data, $domain, 0 );

        } else {
            return false;
        }

    } else {
        $mo->entries = $data['entries'];
        $mo->headers = $data['headers'];
    }

    if ( isset( $l10n[ $domain ] ) ) {
        $mo->merge_with( $l10n[ $domain ] );
    }

    $l10n[ $domain ] = &$mo;

    return true;
}

add_filter( 'override_load_textdomain', 'custom_override_load_textdomain', 1, 3 );
