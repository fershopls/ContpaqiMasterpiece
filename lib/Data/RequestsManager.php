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
                    echo "\nThis file is dead";
                }
                echo "\nSomebody is working with this file";
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
        $file = isset($files[2])?$files[2]:false;
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