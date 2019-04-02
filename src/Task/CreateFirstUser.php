<?php

namespace ZFE\SkeletonInstaller\Task;

use Composer\IO\IOInterface;

class CreateUser extends AbstractTask
{
    public function run(): bool
    {
        //$this->io->write('<info>Создание первого пользователя</info>');
        return true;
    }
}