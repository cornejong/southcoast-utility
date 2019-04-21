<?php

namespace SouthCoast\Utility;

class Temp
{
    const DEFAULT_EXPIRY_TIME = TimeInSeconds::MONTH;

    /**
     * Holds the references to the temp files
     *
     * @var array
     */
    protected $reference_table = [];

    /**
     * @param string $identifier
     * @param mixed $data
     * @param int $expires
     */
    public static function keep(string $identifier, $data, int $expires = Temp::DEFAULT_EXPIRY_TIME)
    {
        if (self::isKnownIdentifier($identifier)) {
            throw new \Exception('Identifier is already in use!', 1);
        }

        $serialized = serialize($data);

        $file_identifier = self::getFileName($identifier, $expires);

        return self::store_data($file_identifier, $serialized);
    }

    /**
     * @param string $identifier
     */
    public static function get(string $identifier)
    {
        if (!self::isKnownIdentifier($identifier)) {
            throw new \Exception('Identifier is unknown!', 1);
        }

        $path = self::getFilePath($identifier);

        if (self::getExpires(self::getFilePath($identifier)) > time()) {
            throw new \Exception('File has expired!', 1);
        }

        $serialized = file_get_contents($path);
        $data = unserialize($serialized);

        return $data;
    }

    /**
     * @param string $identifier
     */
    public static function getFilePath(string $identifier): string
    {
        return self::$reference_table[$identifier];
    }

    /**
     * @param string $path
     * @param $data
     */
    public static function store_data(string $path, $data)
    {
        return file_put_contents($path, $data) === false ? false : true;
    }

    public static function loadReferences()
    {
        /* Get the system directory */
        $directory = self::getDirectory();
        /* Get all the files from the temp directory */
        $files = glob($directory . '*');

        /* Loop over all the files */
        foreach ($files as $file) {
            /* Lets first match the filename */
            if (preg_match('/(.*)(\<\[(\d*)\]\>)/', $file, $matches)) {
                /* Check if the file is still valid */
                if (time() >= $matches[3]) {
                    /* If not, unlink it */
                    unlink($file);
                }
                /* Store it in the reference table */
                self::$reference_table[$matches[1]] = $file;
            }
        }
    }

    public static function cleanup()
    {
        /* Get the system directory */
        $directory = self::getDirectory();
        /* Get all the files from the temp directory */
        $files = glob($directory . '*');

        /* Loop over all the files */
        foreach ($files as $file) {
            /* Lets first match the filename */
            if (preg_match('/(.*)(\<\[(\d*)\]\>)/', $file, $matches)) {
                /* Check if the file is still valid */
                if (time() >= $matches[3]) {
                    /* If not, unlink it */
                    unlink($file);
                }
            }
        }
    }

    public static function flush()
    {
        /* Get the system directory */
        $directory = self::getDirectory();
        /* Get all the files from the temp directory */
        $files = glob($directory . '*');

        /* Loop over all the files */
        foreach ($files as $file) {
            /* Lets first match the filename, just to be sure we only delete files we created */
            if (preg_match('/(.*)(\<\[(\d*)\]\>)/', $file, $matches)) {
                /* Remove the file */
                unlink($file);
            }
        }
    }

    public static function getDirectory(): string
    {
        $directory = self::getFileName('random_prefix', TimeInSeconds::MINUTE);
        return str_replace(basename($directory), '', $directory);
    }

    /**
     * @param string $prefix
     */
    public static function getFileName(string $prefix, int $expires): string
    {
        $file_identifier = $prefix . '<[' . time() + $expires . ']>';
        return tempnam(sys_get_temp_dir(), $file_identifier);
    }

    /**
     * @param string $file_location
     * @param string $identifier
     */
    public static function store_reference(string $file_location, string $identifier, int $expires)
    {
        if (Temp::isKnownIdentifier($identifier)) {
            throw new \Exception('Identifier is already in use!', 1);
        }

        self::$reference_table[$identifier] = $file_location;
    }

    /**
     * @param string $identifier
     */
    public static function isKnownIdentifier(string $identifier)
    {
        return isset(self::$reference_table[$identifier]);
    }

    /**
     * @param $file_identifier
     * @return int|null             The Expiry date in seconds or null if not found.
     */
    public static function getExpires($file_identifier): int
    {
        if (preg_match('/(\<\[(\d*)\]\>)/', $file_identifier, $matches)) {
            return $matches[2];
        }

        return null;
    }
}
