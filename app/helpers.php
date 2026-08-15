<?php

if (!function_exists('to_asset_url')) {
    function to_asset_url($path) {
        if (empty($path)) return '';
        if (str_starts_with($path, 'data:image/') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return url($path);
    }
}
