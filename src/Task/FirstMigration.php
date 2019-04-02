<?php

namespace ZFE\SkeletonInstaller\Task;

class FirstMigration extends AbstractTask
{
    public function run(): bool
    {
        //$this->io->write('<info>Заливка первой версии схемы БД</info>');
        return true;
    }
}