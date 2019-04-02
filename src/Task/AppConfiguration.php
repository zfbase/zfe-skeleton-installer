<?php

namespace ZFE\SkeletonInstaller\Task;

use Composer\IO\IOInterface;

class AppConfiguration extends AbstractTask
{
    public function run(): bool
    {
        $this->io->write('<info>Настройка наименований приложения</info>');

        $shortBrand = $this->io->ask(static::MESSAGE_INDENT . 'Короткое название (рядом с логотипом; пример: ZFE): ', 'ZFE');
        $fullBrand = $this->io->ask(static::MESSAGE_INDENT . 'Короткое название (рядом с логотипом; пример: ZF for Editors): ', 'ZF for Editors');

        $appConfigPath = implode(DIRECTORY_SEPARATOR, [$this->root, 'application', 'configs', 'application.ini']);
        $appConfig = file_get_contents($appConfigPath);

        $fields = [
            'brand.short' => $shortBrand,
            'brand.full' => $fullBrand,
        ];
        foreach ($fields as $field => $value) {
            $appConfig = preg_replace(
                ["/\n{$field}\s*=\s*\"([^\"]*)\"\s*\n/i", "/\ndoctrine\.{$field}\s*=\s*([^\n]*)\s*\n/i"],
                "\n{$field} = {$value}\n",
                $appConfig,
                1 // @todo поменять ограничение на исправления только продакшена на более надежный способ
            );
        }

        file_put_contents($appConfigPath, $appConfig);

        return true;
    }
}