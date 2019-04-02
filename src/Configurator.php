<?php

namespace ZFE\SkeletonInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Factory;
use Traversable;

// wtf autoload bug
include_once __DIR__ . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR . 'Abstract.php';

/**
 * Первичная настройка приложения на ZFE.
 */
class Configurator
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
     * Право на продолжение исполнения задач.
     *
     * @var boolean
     */
    private $exec = true;

    /**
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->root = realpath(dirname(Factory::getComposerFile()));
    }

    public function run()
    {
        $this->runTask('DBConfiguration');
        $this->runTask('FirstMigration');
        $this->runTask('ModelsGeneration');
        $this->runTask('AppConfiguration');
        $this->runTask('CreateFirstUser');
    }

    private function runTask(string $name)
    {
        if (!$this->exec) {
            return; // Если предыдущие задачи провалились, то дальнейшие выполнять тоже не стоит
        }

        try {
            $taskClass = $this->getTaskClass($name);
        } catch(Traversable $t) {
            $taskClass = null;
        }
        
        if ($taskClass) {
            try {
                $task = new $taskClass($this->composer, $this->io, $this->root);
                $result = $task->run();
            } catch (Traversable $t) {
                $result = false;
                $code = $t->getCode();
                $msg = $t->getMessage();
                $this->io->write("<error>Исполнение задачи {$name} ({$taskClass}) закончилось ошибкой [{$code}]: {$msg}</error>");
            }

            $this->exec = $result;
        } else {
            $this->io->write("<error>Задача {$name} не найдена.</error>");
        }
    }

    private function getTaskClass(string $name)
    {
        if (class_exists($name)) {
            return $name;
        }

        $name = ucfirst($name);
        $classFile = str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $name) . '.php';
        $taskDir = __DIR__ . DIRECTORY_SEPARATOR . 'Task';
        $fileName = $taskDir . DIRECTORY_SEPARATOR . $classFile;
        $namespace = __NAMESPACE__ . '\\Task';
        $className = $namespace . '\\' . $name;

        if (is_readable($fileName)) {
            include_once $fileName;
            if (class_exists($className, false)) {
                return $className;
            }
        }
        
        return null;
    }
}
