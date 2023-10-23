# dieselbaby's "preview metadata plugin" for YOURLs

### This is a readme file for how to use my "twitter compliant" preview images/opengraph metadata plugin.

**What do I mean by "twitter compliant", exactly?**

This is a rudimentary plugin (hey, it works) that will let you utilize your current YOURLs configuration and not have to make any breaking changes or install any kind of new libraries or enable any new features in PHP on your server (especially helpful for those who are hosting their YOURLs instance in a shared hosting environment), while also **automatically adding the relevant metadata for opengraph preview images/twitter preview images of the long URL (the link you've shortened in YOURLs), so you can post your shortened vanity link on Twitter (or elsewhere) and still have it properly display the preview image as if it were the full link**

**This is especially helpful for if you are posting referral links, as it will display the vanity URL you've setup in YOURLs with the proper preview image of the underlying page you're redirecting to.**

## Installation guide

**Step 1:** Create a new directory within your `/user/plugins` directory in your YOURLs instance.  Something like `/users/plugins/metadata-plugin` or whatever you fancy.

**Step 2:** Within this directory, create a new `.php` file, called `plugin.php` in a text editor.

**Step 3:** At the top of the file, you'll have to add the plugin header comment that YOURLs expects, for compatability within the backend/admin interface:

```
<?php
/*
Plugin Name: Preview images & metadata plugin
Plugin URI: https://github.com/dieselbabyy/yourls-upgrades/preview-metadata-plugin.md
Description: Automatically retrieves and sets up metadata/preview images for shortened links.
Author: dieselbaby
Author URI: https://github.com/dieselbabyy
Version: 1.0
*/
```
**Step 4:** Define a function within this file whenever a new shortened link is created.  We're going to use the pre-defined `shunt_add_new_link` filter that YOURLs already has setup.  This'll retrieve metadata for the full, longer link and update the shortened link in YOURLs accordingly.  Like this:

```
function metadata_plugin_add_metadata($args) {
    $url = $args[0];
    $keyword = $args[1];

    // Retrieve metadata for the full URL here

    // Update the short URL with the metadata
    yourls_edit_link_title($keyword, $title);
    yourls_edit_link_keyword($keyword, $keyword);
    yourls_edit_link_meta($keyword, 'description', $description);
    yourls_edit_link_meta($keyword, 'image', $image_url);
}
yourls_add_filter('shunt_add_new_link', 'metadata_plugin_add_metadata');
```

**Step 5 (Optional):** ***Note, this step is only optional if you want to take the trouble to use different methods for getting further information on the underlying link.  IF you want to do the quick and dirty way that this "plugin" is intended to be for, just copy the thing at the end of this document under the "quick and dirty copy/paste" section.***

**This is for adding Opengraph data, and requires you to get an API key from Facebook to get responses from their API**  It uses PHP's `file_get_contents` to do this, and you'd add this to the `plugin.pgp` file:
```
function get_open_graph_metadata($url) {
    $response = file_get_contents("https://graph.facebook.com/v12.0/?id=" . urlencode($url) . "&fields=og:title,og:description,og:image&type=article&access_token=YOUR_ACCESS_TOKEN");
    
    $metadata = json_decode($response, true);
    
    $title = $metadata['og:title'];
    $description = $metadata['og:description'];
    $image_url = $metadata['og:image'][0]['url'];
    
    return array(
        'title' => $title,
        'description' => $description,
        'image_url' => $image_url
    );
}
```

Replace the `YOUR_ACCESS_TOKEN` with your API key from Facebook.  You have to create a Facebook app and request permissions for `pages_read_engagement`.

Once you've done this, you would update the `metadata_plugin_add_metadata` function in the `plugin.php` file with this:

```
function metadata_plugin_add_metadata($args) {
    $url = $args[0];
    $keyword = $args[1];

    // Retrieve metadata for the full URL
    $metadata = get_open_graph_metadata($url);
    $title = $metadata['title'];
    $description = $metadata['description'];
    $image_url = $metadata['image_url'];

    // Update the short URL with the metadata
    yourls_edit_link_title($keyword, $title);
    yourls_edit_link_keyword($keyword, $keyword);
    yourls_edit_link_meta($keyword, 'description', $description);
    yourls_edit_link_meta($keyword, 'image', $image_url);
}
```

**NOTE: this doesn't deal with any kind of responses from the API or validation or any of that, this is just a means to an end for people to potentially explore and go down.**  Name `get_open_graph_metadata` with whatever you choose to name it.

## Quick and dirty copy/paste section

***As promised, here is the whole easy to copy/paste thing that has everything you need to make your YOURLs shortened links work on sites like twitter without any problems***

This utilizes the built-in PHP library `SimpleHTMLDom` to get the metadata from the full, longer URL.  You'd add this into the `plugin.php` file underneath the commented out section that says `// Retrieve metadata for the full URL` 

```
// Retrieve metadata for the full URL
$html = file_get_html($url);
$title = $html->find('title', 0)->plaintext;
$description = $html->find('meta[name=description]', 0)->content;
$image_url = $html->find('meta[property=og:image]', 0)->content;
```

**Alternatively, if you're lazy like me - here's the whole thing in one go:**

```
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
```

**tl;dr** Just copy/paste the above block of code into a file called `plugin.php` which you create in the `/users/plugins/metadata-plugin` directory (or whatever you prefer to "metadata-plugin") and after you do this, activate this plugin from the YOURLs admin panel/backend.  You're good to go!

# NOTE: FOR SOME PEOPLE THE ABOVE MAY NOT WORK.  THIS MIGHT BE A BETTER OPTION:

```
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
```
