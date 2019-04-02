<?php

namespace ZFE\SkeletonInstaller\Task;

use function preg_replace;

class DBConfiguration extends AbstractTask
{
    public function run(): bool
    {
        $this->io->write('<info>Настройка подключения к БД</info>');

        $hostDefault = '127.0.0.1';
        $portDefault = '3306';
        $userDefault = 'zfe-project';

        $host = $this->io->ask(static::MESSAGE_INDENT . "Хост (по умолчанию {$hostDefault}): ", $hostDefault);
        $port = $this->io->ask(static::MESSAGE_INDENT . "Порт (по умолчанию {$portDefault}): ", $portDefault);
        $user = $this->io->ask(static::MESSAGE_INDENT . "Пользователь (по умолчанию {$userDefault}): ", $userDefault);
        $pass = $this->io->ask(static::MESSAGE_INDENT . 'Пароль: ');

        $baseDefault = $user;
        $base = $this->io->ask(static::MESSAGE_INDENT . "База (по умолчанию {$baseDefault}): ", $baseDefault);

        $doctrineConfigPath = implode(DIRECTORY_SEPARATOR, [$this->root, 'application', 'configs', 'doctrine.ini']);
        $doctrineConfig = file_get_contents($doctrineConfigPath);

        $fields = [
            'host' => $host,
            'port' => $port,
            'username' => $user,
            'password' => $pass,
            'schema' => $base,
        ];
        foreach ($fields as $field => $value) {
            $doctrineConfig = preg_replace(
                ["/\ndoctrine\.{$field}\s*=\s*\"([^\"]*)\"\s*\n/i", "/\ndoctrine\.{$field}\s*=\s*([^\n]*)\s*\n/i"],
                "\ndoctrine.{$field} = \"{$value}\"\n",
                $doctrineConfig,
                1 // @todo поменять ограничение на исправления только продакшена на более надежный способ
            );
        }

        file_put_contents($doctrineConfigPath, $doctrineConfig);

        return true;
    }
}