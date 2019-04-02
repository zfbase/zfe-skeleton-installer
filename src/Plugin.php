<?php

namespace ZFE\SkeletonInstaller;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event as ScriptEvent;

/**
 * Undocumented class
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'post-install-cmd' => [
                ['configurator'],
                ['uninstallPlugin'],
            ],
        ];
    }

    /**
     * Настройщик нового приложения.
     *
     * @param ScriptEvent $event
     */
    public function configurator(ScriptEvent $event)
    {
        $configurator = new Configurator($this->composer, $this->io);
        $configurator->run();
    }

    /**
     * Remove the installer after project installation.
     *
     * @param ScriptEvent $event
     */
    public function uninstallPlugin(ScriptEvent $event)
    {
        $uninstall = new Uninstaller($this->composer, $this->io);
        $uninstall->run();
    }
}
