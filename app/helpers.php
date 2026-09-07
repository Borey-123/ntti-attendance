<?php

if (!function_exists('to_asset_url')) {
    function to_asset_url($path) {
        if (empty($path)) return '';
        if (str_starts_with($path, 'data:image/') || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $trimmed = ltrim($path, '/');
        if (str_starts_with($trimmed, 'teachers/') || str_starts_with($trimmed, 'portal/')) {
            return url('storage/' . $trimmed);
        }
        return url($trimmed);
    }
}
