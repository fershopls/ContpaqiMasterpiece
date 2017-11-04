<?php

namespace lib\Data;

class ConfigDirectoryManager {

    const CONFIG_FILE_EXTENSION = 'php';
    protected $path;

    public function __construct($configDirectoryPath)
    {
        $this->setConfigDirectory($configDirectoryPath);
    }

    public function setConfigDirectory ($configDirectoryPath)
    {
        if ($this->isValidConfigDirectory($configDirectoryPath))
            $this->path = $configDirectoryPath;
    }

    protected function isValidConfigDirectory($configDirectoryPath)
    {
        return file_exists($configDirectoryPath) && is_dir($configDirectoryPath);
    }

    public function get($indexes, $fallback = array())
    {
        list($configFile, $arrayRoute) = $this->indexize($indexes);
        $fileSettingsArray = $this->getFileContentOf($this->findConfigFileRealpath(strtolower($configFile)));

        if ($arrayRoute == '')
            return $fileSettingsArray;

        $settingsManager = new SettingsManager($fileSettingsArray);
        return $settingsManager->get($arrayRoute, $fallback);
    }

    protected function indexize ($indexes)
    {
        $arrayRoute = explode('.', $indexes, 2);
        if (count($arrayRoute) == 0)
            return ['',''];
        elseif (count($arrayRoute) == 1)
            return [$arrayRoute[0], ''];
        else
            return $arrayRoute;
    }

    protected function findConfigFileRealpath ($configFile)
    {
        return $this->path.DIRECTORY_SEPARATOR.$configFile.'.'.self::CONFIG_FILE_EXTENSION;
    }

    protected function getFileContentOf ($configFilePath)
    {
        if ($this->isValidConfigFile($configFilePath))
            return include ($configFilePath);
    }

    protected function isValidConfigFile($configFilePath)
    {
        return file_exists($configFilePath);
    }
}