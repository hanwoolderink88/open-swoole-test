<?php

namespace User\Swoole\Infrastructure\Persistence;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use User\Swoole\Infrastructure\Container\Application;

class EntityManagerFactory
{
    public static function create():EntityManager
    {
        $app = Application::getInstance();

        $basePath = $app->getBasePath();

        // Create a simple "default" Doctrine ORM configuration for Attributes
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: array($basePath . "/src"),
            isDevMode: true,
        );

        // configuring the database connection
        $connection = DriverManager::getConnection([
            'dbname' => 'schema',
            'user' => 'root',
            'password' => 'root',
            'host' => 'db',
            'driver' => 'pdo_mysql',
        ], $config);

        // obtaining the entity manager
        return new EntityManager($connection, $config);
    }
}