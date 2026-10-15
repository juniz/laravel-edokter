<?php

if (!function_exists('clean')) {
    /**
     * Sanitize HTML content or provide fallback if HTMLPurifier is not bound.
     *
     * @param mixed $dirty
     * @param mixed $config
     * @param \Closure|null $postCreateConfigHook
     * @return mixed
     */
    function clean($dirty, $config = null, ?\Closure $postCreateConfigHook = null)
    {
        if (function_exists('app') && app()->bound('purifier')) {
            return app('purifier')->clean($dirty, $config, $postCreateConfigHook);
        }

        if (is_array($dirty)) {
            return array_map('clean', $dirty);
        }

        if ($dirty === null || $dirty === '') {
            return '';
        }

        return strip_tags((string) $dirty, '<p><br><b><strong><i><em><u><ul><ol><li><table><thead><tbody><tr><td><th><a><h1><h2><h3><h4><h5><h6><img><span><div><hr><pre><code><blockquote>');
    }
}
