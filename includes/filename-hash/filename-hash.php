<?php
/**
 * Plugin Name: Hash Upload Filename
 * Plugin URI: http://stackoverflow.com/questions/3259696
 * Description: Rename uploaded files as the hash of their original.
 * Version: 0.1
 */

/**
 * Filter {@see sanitize_file_name()} and return an MD5 hash.
 *
 * @param string $filename
 * @return string
 */
function make_filename_hash($filename) {
    $info = pathinfo($filename);
    $ext  = empty($info['extension']) ? '' : '.' . $info['extension'];
    $name = basename($filename, $ext);
    return md5($name) . $ext;
}
add_filter('sanitize_file_name', 'make_filename_hash', 10);