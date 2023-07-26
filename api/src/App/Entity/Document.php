<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\Table(name: "documents")]
class Document
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface|string $id;

    #[ORM\Column(name: 'title', type: Types::STRING, length: 255, nullable: false)]
    private string $title;

    #[ORM\Column(name: 'pdf_file', type: Types::STRING, length: 255, nullable: false)]
    private string $pdfFile;

    private array $states; // array collection

    private string $publicationDate;

    private string $signingDate;

    /**
     * @param string $pdfFile
     * @param string $title
     */
    public function __construct(string $title, string $pdfFile)
    {
        $this->id = Uuid::uuid4();
        $this->title = $title;
        $this->pdfFile = $pdfFile;
    }

    /**
     * Named constructor
     *
     * @param array $data
     * @return Document
     */
    public static function fromArray(array $data): Document
    {
        return new self(
            $data['title'],
            $data['pdfFile']
        );
    }

    /**
     * @return UuidInterface|string
     */
    public function getId(): UuidInterface|string
    {
        return $this->id;
    }

    /**
     * @param UuidInterface|string $id
     */
    public function setId(UuidInterface|string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * @param array $states
     */
    public function setStates(array $states): void
    {
        $this->states = $states;
    }

    /**
     * @return string
     */
    public function getPublicationDate(): string
    {
        return $this->publicationDate;
    }

    /**
     * @param string $publicationDate
     */
    public function setPublicationDate(string $publicationDate): void
    {
        $this->publicationDate = $publicationDate;
    }

    /**
     * @return string
     */
    public function getPdfFile(): string
    {
        return $this->pdfFile;
    }

    /**
     * @param string $pdfFile
     */
    public function setPdfFile(string $pdfFile): void
    {
        $this->pdfFile = $pdfFile;
    }

    /**
     * @return string
     */
    public function getSigningDate(): string
    {
        return $this->signingDate;
    }

    /**
     * @param string $signingDate
     */
    public function setSigningDate(string $signingDate): void
    {
        $this->signingDate = $signingDate;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function toArray(): array
    {
        return [
            'id'      => $this->getId(),
            'title'   => $this->getTitle(),
            'pdfFile' => '',
            'states'  => [],
            "publicationDate" => null,
            "signingDate"     => null,
        ];
    }
}
