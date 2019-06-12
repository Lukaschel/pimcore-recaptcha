<?php
/**
 * PimcoreRecaptchaBundle
 * Copyright (c) Lukaschel
 */

namespace Lukaschel\PimcoreRecaptchaBundle;

use Exception;
use Lukaschel\PimcoreRecaptchaBundle\Tool\Install;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Pimcore\HttpKernel\Bundle\DependentBundleInterface;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Pimcore\Routing\RouteReferenceInterface;

class PimcoreRecaptchaBundle extends AbstractPimcoreBundle implements DependentBundleInterface
{
    use PackageVersionTrait;

    const PACKAGE_NAME = 'lukaschel/pimcore-recaptcha';

    private static $bundles = [
        'Lukaschel\\PimcoreConfigurationBundle\\PimcoreConfigurationBundle',
    ];

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.1';
    }

    /**
     * @param BundleCollection $collection
     *
     * @throws Exception
     */
    public static function registerDependentBundles(BundleCollection $collection)
    {
        if (!empty(self::$bundles)) {
            foreach (self::$bundles as $bundle) {
                if (!class_exists($bundle)) {
                    throw new Exception(sprintf('%s is not available', $bundle));
                }
            }
            $collection->addBundles(self::$bundles);
        }
    }

    /**
     * @return RouteReferenceInterface|string|void|null
     */
    public function getAdminIframePath()
    {
        return '/admin/pimcoreconfiguration/bundle' . str_replace(__NAMESPACE__, '', __CLASS__);
    }

    /**
     * @return array
     */
    public static function getDependentBundles()
    {
        return self::$bundles;
    }

    /**
     * @return mixed
     */
    public function getInstaller()
    {
        return $this->container->get(Install::class);
    }

    /**
     * @return string
     */
    protected function getComposerPackageName(): string
    {
        return self::PACKAGE_NAME;
    }
}
