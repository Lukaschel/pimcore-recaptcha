<?php
/**
 * PimcoreRecaptchaBundle
 * Copyright (c) Lukaschel
 */

namespace Lukaschel\PimcoreRecaptchaBundle\Tool;

use Exception;
use Lukaschel\PimcoreRecaptchaBundle\Configuration\Configuration;
use Lukaschel\PimcoreRecaptchaBundle\PimcoreRecaptchaBundle;
use OutOfBoundsException;
use PackageVersions\Versions;
use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\Extension\Bundle\PimcoreBundleManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class Install extends AbstractInstaller
{
    /**
     * @var PimcoreBundleManager
     */
    protected $bundleManager;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var string
     */
    private $installSourcesPath;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $currentVersion;

    /**
     * Install constructor.
     *
     * @param PimcoreBundleManager $bundleManager
     */
    public function __construct(
        PimcoreBundleManager $bundleManager
    ) {
        parent::__construct();

        $this->bundleManager = $bundleManager;
        $this->installSourcesPath = __DIR__ . '/../Resources/install';
        $this->fileSystem = new Filesystem();
        try {
            $this->currentVersion = Versions::getVersion(PimcoreRecaptchaBundle::PACKAGE_NAME);
        } catch (OutOfBoundsException $e) {
            $bundle = new PimcoreRecaptchaBundle();
            $this->currentVersion = $bundle->getVersion();
        }
    }

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return bool|void
     */
    public function install()
    {
        $this->installDependentBundles();
        $this->installOrUpdateConfigFile();
        $this->installBundleConfigFile();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function update()
    {
        $this->installOrUpdateConfigFile();
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {
        if ($this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH)) {
            $this->fileSystem->remove(
                Configuration::SYSTEM_CONFIG_FILE_PATH
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return $this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function canBeInstalled()
    {
        return !$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUninstalled()
    {
        return $this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function needsReloadAfterInstall()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUpdated()
    {
        $needUpdate = false;
        if ($this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH)) {
            $config = Yaml::parse(file_get_contents(Configuration::SYSTEM_CONFIG_FILE_PATH));
            if ($config['version'] !== $this->currentVersion) {
                $needUpdate = true;
            }
        }

        return $needUpdate;
    }

    /**
     * install bundle config file
     */
    private function installBundleConfigFile()
    {
        if (!$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_DIR_PATH)) {
            $this->fileSystem->mkdir(Configuration::SYSTEM_CONFIG_DIR_PATH);
        }
    }

    /**
     * install / update config file.
     */
    private function installOrUpdateConfigFile()
    {
        if (!$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_DIR_PATH)) {
            $this->fileSystem->mkdir(Configuration::SYSTEM_CONFIG_DIR_PATH);
        }

        $config = ['version' => $this->currentVersion];
        $yml = Yaml::dump($config);
        file_put_contents(Configuration::SYSTEM_CONFIG_FILE_PATH, $yml);
    }

    /**
     * Install dependent bundles
     */
    private function installDependentBundles()
    {
        $bundles = PimcoreRecaptchaBundle::getDependentBundles();

        foreach ($bundles as $bundle) {
            try {
                $bundleObject = $this->bundleManager->getActiveBundle($bundle, false);
                if ($bundleObject->getInstaller() and
                    !$bundleObject->getInstaller()->isInstalled() and
                    $bundleObject->getInstaller()->canBeInstalled()
                ) {
                    $bundleObject->getInstaller()->install();
                }
            } catch (Exception $exception) {
            }
        }
    }
}
