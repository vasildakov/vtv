<?php

declare(strict_types=1);

namespace VTV\Model;

/**
 * Class Document
 *
 * @author Vasil Dakov <vasil.dakov@digitaspixelpark.com>
 */
class Document
{
    private string $id;

    private string $title;

    private array $states;

    private string $publicationDate;

    private string $pdfFile;

    private string $signingDate;

    public function __construct(
        string $id,
        string $title,
        string $pdfFile,
        string $publicationDate,
        array $states
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->pdfFile = $pdfFile;
        $this->publicationDate = $publicationDate;
        $this->states = $states;
    }


    public function toArray(): array
    {
        return get_object_vars( $this );
    }
}
