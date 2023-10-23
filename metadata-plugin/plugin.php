<?php
/*
Plugin Name: Preview images & metadata plugin
Plugin URI: https://github.com/dieselbabyy/yourls-upgrades/preview-metadata-plugin.md
Description: Automatically retrieves and sets up metadata/preview images for shortened links.
Author: dieselbaby
Author URI: https://github.com/dieselbabyy
Version: 1.0.1
*/

yourls_add_filter('shunt_get_remote_content', 'metadata_plugin_get_remote_content');
function metadata_plugin_get_remote_content($return, $url) {
    // Retrieve the remote content using cURL or any other method you prefer
    $remote_content = yourls_remote_get($url);
    
    // Parse the remote content to extract the required metadata
    $title = metadata_plugin_get_title($remote_content);
    $description = metadata_plugin_get_description($remote_content);
    $image = metadata_plugin_get_image($remote_content);
    
    // Set the metadata for the full link
    yourls_set_keyword_meta($url, 'title', $title);
    yourls_set_keyword_meta($url, 'description', $description);
    yourls_set_keyword_meta($url, 'image', $image);
    
    // Return the original remote content
    return $return;
}

function metadata_plugin_get_title($content) {
    preg_match('/<title>(.*?)<\/title>/i', $content, $matches);
    if (isset($matches[1])) {
        return $matches[1];
    } else {
        return '';
    }
}

function metadata_plugin_get_description($content) {
    preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']\s*\/?>/i', $content, $matches);
    if (isset($matches[1])) {
        return $matches[1];
    } else {
        return '';
    }
}

function metadata_plugin_get_image($content) {
    preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\'](.*?)["\']\s*\/?>/i', $content, $matches);
    if (isset($matches[1])) {
        return $matches[1];
    } else {
        return '';
    }
}