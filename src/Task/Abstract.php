<?php

namespace ZFE\SkeletonInstaller\Task;

use Composer\Composer;
use Composer\IO\IOInterface;

abstract class AbstractTask
{
    const MESSAGE_INDENT = '  - ';

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * Корень приложения.
     *
     * @var string
     */
    protected $root;

    /**
     * @param IOInterface $io
     */
    public function __construct(Composer $composer, IOInterface $io, string $root)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->root = $root;
    }

    /**
     * @return boolean Продолжить выполнение следующих задач?
     */
    abstract public function run(): bool;
}