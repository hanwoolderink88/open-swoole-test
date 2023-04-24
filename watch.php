<?php
// Open an inotify instance
$fd = inotify_init();

// Watch __FILE__ for metadata changes (e.g. mtime)
inotify_add_watch_recursive($fd, __DIR__ . '/src', IN_MODIFY | IN_MOVED_FROM | IN_CREATE | IN_DELETE | IN_ISDIR);
inotify_add_watch_recursive($fd, __DIR__ . '/resources', IN_MODIFY | IN_MOVED_FROM | IN_CREATE | IN_DELETE | IN_ISDIR);

while (true) {
    // Read events
    $events = inotify_read($fd);

    echo "File changed, reloading server \n";

    $file = fopen(__DIR__ . '/shaNew', 'wa+');
    fwrite($file, sha1((new DateTime())->format('Y-m-d H:i:s')));
}

function inotify_add_watch_recursive($inotify, $path, $mask)
{
    $ids = [inotify_add_watch($inotify, $path, $mask)];

    if (is_dir($path)) {
        foreach (glob($path . '/*', GLOB_ONLYDIR) as $subdir) {
            $ids = inotify_add_watch_recursive($inotify, $subdir, $mask);

            $ids = array_merge($ids, $ids);
        }
    }
    return $ids;
}
