<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class GetWageAgreementFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): GetWageAgreement
    {
        return new GetWageAgreement(
            $container->get(EntityManager::class)
        );
    }
}
