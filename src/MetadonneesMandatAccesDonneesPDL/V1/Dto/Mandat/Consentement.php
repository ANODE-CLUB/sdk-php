<?php

namespace AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat;

readonly class Consentement
{
    public function __construct(
        public \DateTimeImmutable $donneLe,
        public \DateTimeImmutable $expireLe
    ) {
        if ($donneLe > $expireLe) {
            throw new \InvalidArgumentException('La date à laquelle le consentement a été donné doit être antérieure à son expiration.');
        }
    }

    public function buildXml(
    ): string {
        $xml = '<consentement>' . "\n";
        $xml .= '  <donneLe>' . $this->donneLe->format('c') . '</donneLe>' . "\n";
        $xml .= '  <expireLe>' . $this->expireLe->format('c') . '</expireLe>' . "\n";
        $xml .= '</consentement>' . "\n";

        return $xml;
    }

    public static function makeFromXml(
        \SimpleXMLElement $consentement
    ): self {
        return new self(
            new \DateTimeImmutable((string) $consentement->donneLe),
            new \DateTimeImmutable((string) $consentement->expireLe)
        );
    }
}
