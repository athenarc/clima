<?php

namespace app\components;


class ProjectDiff
{
    public static function str($old, $new)
    {
        return '<span class="text-danger">' . ((empty($old) && (int)$old !== 0) ? '\'\'' : $old) . '</span>&#8594<strong class="text-info">' . ((empty($new) && $new !== 0) ? '\'\'' : $new) . '</strong>';
    }

    public static function arr($old, $new)
    {
        $deleted = [];
        $existing = [];
        $created = [];
        foreach ($new as $newElement) {
            if (in_array($newElement, $old)) {
                $existing[] = $newElement;
            } else {
                $created[] = '<strong>'.$newElement.'</strong>';
            }
        }
        foreach ($old as $oldElement) {
            if (!in_array($oldElement, $existing)) {
                $deleted[] = '<s>' . $oldElement . '</s>';
            }
        }

        $htmlComponents = [];
        if (!empty($deleted)) $htmlComponents[] = '<span class="text-danger">' . join(', ', $deleted) . '</span>';
        if (!empty($existing)) $htmlComponents[] = '<span>' . join(', ', $existing) . '</span>';
        if (!empty($created)) $htmlComponents[] = '<span class="text-info">' . join(', ', $created) . '</span>';

        return join(', ', $htmlComponents);
    }
}