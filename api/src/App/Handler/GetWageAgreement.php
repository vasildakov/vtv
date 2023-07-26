<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Document;
use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GetWageAgreement implements RequestHandlerInterface
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];

        $repository = $this->em->getRepository(Document::class);

        $id = (string) $request->getAttribute('id');
        if ($id) {
            $record = $repository->findOneBy(['id' => $id]);
            return new JsonResponse([
                'success' => true,
                'data' => $record->toArray()
            ]);
        }

        $records = $repository->findAll();

        foreach ($records as $record) {
            $data[] = $record->toArray();
        }

        /*$data = [
            "id" => "V27200011487",
            "documents" => [
                [
                    "id" => "coremedia:///cap/content/128490",
                    "path" => "/bmas/DE/Geschuetzter-Bereich/Tarifregister/Bundesweit/V27200011487",
                    "version" => "coremedia:///cap/version/128490/1"
                ]
            ]
        ]; */

        return new JsonResponse([
            'success' => true,
            'data'    => $data
        ]);
    }
}
