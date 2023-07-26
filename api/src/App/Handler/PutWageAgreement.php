<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Document;
use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Class PutWageAgreement
 *
 * @author Vasil Dakov <vasildakov@gmail.com>
 * @copyright 2009-2023 Neutrino.bg
 * @version 1.0
 */
class PutWageAgreement implements RequestHandlerInterface
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id   = (string) $request->getAttribute('id');
        $data = json_decode($request->getBody()->getContents(), true);

        try {

            // decode pdf and save it as a file
            $pdf = base64_decode($data['pdfFile']);
            file_put_contents(
                filename: './data/pdf/'.$data['id'].'.pdf',
                data: $pdf
            );

            $document = new Document($data['title'], $data['pdfFile']);
            $this->em->persist($document);
            $this->em->flush();

            $success = true;
            $message = 'Document sent successfully';

        } catch (Throwable $e) {
            $success = false;
            $message = $e->getMessage();
            $data = [];
        }

        return new JsonResponse([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ]);
    }
}
