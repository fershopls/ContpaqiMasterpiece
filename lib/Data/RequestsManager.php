<?php

namespace lib\Data;

use Phine\Path\Path;

class RequestsManager {

    const OPEN_MARK = 'opened_at';
    const DEATH_TIME = 30*60;

    protected $file;
    protected $defaults;

    public function get ($arrayDefaults)
    {
        $this->defaults = $arrayDefaults;
        return $this;
    }

    public function on ($stringPath)
    {
        if (file_exists($stringPath) && is_dir($stringPath))
        {
            $file = $this->getFirstFile($stringPath);
            if (!$file) return False;

            if (isset($file['assoc'][self::OPEN_MARK]))
            {
                if (time() >= $file['assoc'][self::OPEN_MARK] + self::DEATH_TIME)
                {
                    echo "\nThis file is dead ({$file['filepath']}).";
                }
                echo "\nThis file was opened previously ({$file['filepath']}).";
            } else {
                $file['assoc'][self::OPEN_MARK] = time();
                file_put_contents($file['filepath'], json_encode($file['assoc'], JSON_PRETTY_PRINT));
                return array_merge($this->defaults, $file['assoc']);
            }
        }
        return False;
    }

    public function getFirstFile ($stringPath)
    {
        $files = scandir($stringPath);
        // Find file
        if (count($files) >= 3)
        {
            array_shift($files);
            array_shift($files);
            do {
                $file = array_shift($files);
            } while (is_dir(Path::join([$stringPath, $file]))); // Find a not directory file
        } else { $file = false; }
        // Process
        if ($file)
        {
            $file = array(
                'filename' => $file,
                'filepath' => Path::join([$stringPath, $file]),
            );
            $file['content'] = file_get_contents($file['filepath']);
            $file['assoc'] = json_decode($file['content'], true);
            $this->file = $file;
        }
        return $file;
    }

    public function delete()
    {
        if ($this->file)
            unlink($this->file['filepath']);
    }

}