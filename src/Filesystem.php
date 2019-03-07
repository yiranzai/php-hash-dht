<?php
/**
 * Created by PhpStorm.
 * User: yiranzai
 * Date: 19-3-6
 * Time: 下午6:32
 */

namespace Yiranzai\Dht;

use FilesystemIterator;

class Filesystem
{

    /**
     * Determine if a file or directory exists.
     *
     * @param  string $path
     * @return bool
     */
    public function exists($path): bool
    {
        return file_exists($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param  string $path
     * @param  bool   $lock
     * @return string
     */
    public function get($path, $lock = false): string
    {
        if ($this->isFile($path)) {
            return $lock ? $this->sharedGet($path) : file_get_contents($path);
        }

        throw new \RuntimeException("File does not exist at path {$path}");
    }

    /**
     * Get contents of a file with shared access.
     *
     * @param  string $path
     * @return string
     */
    public function sharedGet($path): string
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle !== false) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);

                    $contents = fread($handle, $this->size($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }

    /**
     * Get the returned value of a file.
     *
     * @param  string $path
     * @return mixed
     *
     */
    public function getRequire($path)
    {
        if ($this->isFile($path)) {
            return require $path;
        }

        throw new \RuntimeException("File does not exist at path {$path}");
    }

    /**
     * Require the given file once.
     *
     * @param  string $file
     * @return void
     */
    public function requireOnce($file): void
    {
        require_once $file;
    }

    /**
     * Get the MD5 hash of the file at the given path.
     *
     * @param  string $path
     * @return string
     */
    public function hash($path): string
    {
        return md5_file($path);
    }

    /**
     * Write the contents of a file.
     *
     * @param  string $path
     * @param  string $contents
     * @param  bool   $lock
     * @return int
     */
    public function put($path, $contents, $lock = false): int
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Prepend to a file.
     *
     * @param  string $path
     * @param  string $data
     * @return int
     */
    public function prepend($path, $data): int
    {
        if ($this->exists($path)) {
            return $this->put($path, $data . $this->get($path));
        }

        return $this->put($path, $data);
    }

    /**
     * Append to a file.
     *
     * @param  string $path
     * @param  string $data
     * @return int
     */
    public function append($path, $data): int
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * Get or set UNIX mode of a file or directory.
     *
     * @param  string $path
     * @param  int    $mode
     * @return mixed
     */
    public function chmod($path, $mode = null)
    {
        if ($mode !== null) {
            return chmod($path, $mode);
        }

        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array $paths
     * @return bool
     */
    public function delete($paths): bool
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (!@unlink($path)) {
                    $success = false;
                }
            } catch (\Exception $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Move a file to a new location.
     *
     * @param  string $path
     * @param  string $target
     * @return bool
     */
    public function move($path, $target): bool
    {
        return rename($path, $target);
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string $path
     * @param  string $target
     * @return bool
     */
    public function copy($path, $target): bool
    {
        return copy($path, $target);
    }

    /**
     * Create a hard link to the target file or directory.
     *
     * @param  string $target
     * @param  string $link
     * @return void
     */
    public function link($target, $link): void
    {
        if (!$this->windowsOs()) {
            symlink($target, $link);
        }

        $mode = $this->isDirectory($target) ? 'J' : 'H';

        exec("mklink /{$mode} \"{$link}\" \"{$target}\"");
    }

    /**
     * Extract the file name from a file path.
     *
     * @param  string $path
     * @return string
     */
    public function name($path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Extract the trailing name component from a file path.
     *
     * @param  string $path
     * @return string
     */
    public function basename($path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * Extract the parent directory from a file path.
     *
     * @param  string $path
     * @return string
     */
    public function dirname($path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param  string $path
     * @return string
     */
    public function extension($path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type of a given file.
     *
     * @param  string $path
     * @return string
     */
    public function type($path): string
    {
        return filetype($path);
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param  string $path
     * @return string|false
     */
    public function mimeType($path)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string $path
     * @return int
     */
    public function size($path): int
    {
        return filesize($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string $path
     * @return int
     */
    public function lastModified($path): int
    {
        return filemtime($path);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param  string $directory
     * @return bool
     */
    public function isDirectory($directory): bool
    {
        return is_dir($directory);
    }

    /**
     * Determine if the given path is readable.
     *
     * @param  string $path
     * @return bool
     */
    public function isReadable($path): bool
    {
        return is_readable($path);
    }

    /**
     * Determine if the given path is writable.
     *
     * @param  string $path
     * @return bool
     */
    public function isWritable($path): bool
    {
        return is_writable($path);
    }

    /**
     * Determine if the given path is a file.
     *
     * @param  string $file
     * @return bool
     */
    public function isFile($file): bool
    {
        return is_file($file);
    }

    /**
     * Find path names matching a given pattern.
     *
     * @param  string $pattern
     * @param  int    $flags
     * @return array
     */
    public function glob($pattern, $flags = 0): array
    {
        return glob($pattern, $flags);
    }

    /**
     * Create a directory.
     *
     * @param  string $path
     * @param  int    $mode
     * @param  bool   $recursive
     * @param  bool   $force
     * @return bool
     */
    public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false): bool
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * Move a directory.
     *
     * @param  string $from
     * @param  string $to
     * @param  bool   $overwrite
     * @return bool
     */
    public function moveDirectory($from, $to, $overwrite = false): bool
    {
        if ($overwrite && $this->isDirectory($to) && !$this->deleteDirectory($to)) {
            return false;
        }

        return @rename($from, $to) === true;
    }

    /**
     * Copy a directory from one location to another.
     *
     * @param  string $directory
     * @param  string $destination
     * @param  int    $options
     * @return bool
     */
    public function copyDirectory($directory, $destination, $options = null): bool
    {
        if (!$this->isDirectory($directory)) {
            return false;
        }

        $options = $options ?: FilesystemIterator::SKIP_DOTS;

        if (!$this->isDirectory($destination)) {
            $this->makeDirectory($destination, 0777, true);
        }

        $items = new FilesystemIterator($directory, $options);

        foreach ($items as $item) {
            $target = $destination . '/' . $item->getBasename();

            if (!$item->isDir()) {
                if (!$this->copy($item->getPathname(), $target)) {
                    return false;
                }
            } else {
                $path = $item->getPathname();

                if (!$this->copyDirectory($path, $target, $options)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Recursively delete a directory.
     *
     * The directory itself may be optionally preserved.
     *
     * @param  string $directory
     * @param  bool   $preserve
     * @return bool
     */
    public function deleteDirectory($directory, $preserve = false): bool
    {
        if (!$this->isDirectory($directory)) {
            return false;
        }

        $items = new FilesystemIterator($directory);

        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                $this->deleteDirectory($item->getPathname());
            } else {
                $this->delete($item->getPathname());
            }
        }

        if (!$preserve) {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param  string $directory
     * @return bool
     */
    public function cleanDirectory($directory): bool
    {
        return $this->deleteDirectory($directory, true);
    }

    /**
     * Determine whether the current environment is Windows based.
     *
     * @return bool
     */
    public function windowsOs(): bool
    {
        return stripos(PHP_OS, 'win') === 0;
    }
}
