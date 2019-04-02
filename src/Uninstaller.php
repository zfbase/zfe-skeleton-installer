<?php

namespace ZFE\SkeletonInstaller;

use Composer\Composer;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\Factory;
use Composer\Json\JsonFile;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Repository\RepositoryInterface;

// wtf autoload bug
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Collection.php';

/**
 * Средство удаления инсталятора из скелетона.
 * 
 * По образу и подобию Zend\SkeletonInstaller\Uninstaller
 */
class Uninstaller
{
    const PLUGIN_NAME = 'zfbase/zfe-skeleton-installer';
    const MESSAGE_INDENT = '  - ';

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */

    private $io;

    /**
     * @param Composer $composer
     * @param IOInterface $io
     */

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function run()
    {
        $this->io->write(sprintf('<info>Удаление инсталлятора</info>'));
        $this->removePluginInstall();
        $this->removePluginFromComposer();
    }

    private function removePluginInstall()
    {
        $installer = $this->composer->getInstallationManager();
        $repository = $this->composer->getRepositoryManager()->getLocalRepository();
        $package = $repository->findPackage(self::PLUGIN_NAME, '*');

        if (!$package) {
            $this->io->write(sprintf(self::MESSAGE_INDENT . 'Плагин %s не установлен.', self::PLUGIN_NAME));
            return;
        }

        $installer->uninstall($repository, new UninstallOperation($package));
        $this->updateLockFile($repository);
    }

    private function updateLockFile(RepositoryInterface $repository)
    {
        $locker = $this->composer->getLocker();

        $allPackages = Collection::create($repository->getPackages())
            ->reject(function ($package) {
                return self::PLUGIN_NAME === $package->getName();
            });

        $aliases = $allPackages->filter(function ($package) {
            return $package instanceof AliasPackage;
        });

        $devPackages = $allPackages->filter(function ($package) {
            return $package->isDev();
        });

        $packages = $allPackages->filter(function ($package) {
            return !$package instanceof AliasPackage && !$package->isDev();
        });

        $platformReqs = $locker->getPlatformRequirements(false);
        $platformDevReqs = array_diff($locker->getPlatformRequirements(true), $platformReqs);

        $result = $locker->setLockData(
            $packages->toArray(),
            $devPackages->toArray(),
            $platformReqs,
            $platformDevReqs,
            $aliases->toArray(),
            $locker->getMinimumStability(),
            $locker->getStabilityFlags(),
            $locker->getPreferStable(),
            $locker->getPreferLowest(),
            $locker->getPlatformOverrides()
        );

        if (!$result) {
            $this->io->write(sprintf(self::MESSAGE_INDENT . '<error>Не удалось обновить файл блокировки после удаления %s.</error>', self::PLUGIN_NAME));
        }
    }

    private function removePluginFromComposer()
    {
        $composerJson = new JsonFile(Factory::getComposerFile());
        $json = $composerJson->read();
        unset($json['require'][self::PLUGIN_NAME]);
        $composerJson->write($json);
    }
}
