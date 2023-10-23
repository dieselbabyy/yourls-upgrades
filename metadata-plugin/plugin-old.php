<?php
/*
Plugin Name: Preview images & metadata plugin
Plugin URI: https://github.com/dieselbabyy/yourls-upgrades/preview-metadata-plugin.md
Description: Automatically retrieves and sets up metadata/preview images for shortened links.
Author: dieselbaby
Author URI: https://github.com/dieselbabyy
Version: 1.0
*/

function metadata_plugin_add_metadata($args) {
    $url = $args[0];
    $keyword = $args[1];

// Retrieve metadata for the full URL
    
    $html = file_get_html($url);
    $title = $html->find('title', 0)->plaintext;
    $description = $html->find('meta[name=description]', 0)->content;
    $image_url = $html->find('meta[property=og:image]', 0)->content;

// Update the short URL with the metadata
    yourls_edit_link_title($keyword, $title);
    yourls_edit_link_keyword($keyword, $keyword);
    yourls_edit_link_meta($keyword, 'description', $description);
    yourls_edit_link_meta($keyword, 'image', $image_url);
}
yourls_add_filter('shunt_add_new_link', 'metadata_plugin_add_metadata');