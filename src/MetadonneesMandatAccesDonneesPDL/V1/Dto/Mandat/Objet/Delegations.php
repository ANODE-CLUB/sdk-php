<?php

namespace Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet;

class Delegations
{
    public array $delegations;

    public function __construct(
    ) {
        $this->delegations = [];
    }

    public function add(
        string $delegation
    ): self {
        if (!$delegation) {
            throw new \InvalidArgumentException('La délégation est obligatoire.');
        }

        $this->delegations[] = $delegation;

        return $this;
    }

    public function iterate(
    ): \Generator {
        foreach ($this->delegations as $delegation) {
            yield $delegation;
        }
    }

    public function buildXml(
    ): string {
        $xml = '  <delegations>' . "\n";
        foreach ($this->iterate() as $delegation) {
            $xml .= '    <delegation>' . htmlspecialchars($delegation, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</delegation>' . "\n";
        }
        $xml .= '  </delegations>' . "\n";

        return $xml;
    }

    public static function makeFromXml(
        \SimpleXMLElement $delegations
    ): ?self {
        $instance = null;

        if (isset($delegations)) {
            $instance = new self();
            foreach ($delegations->delegation as $delegation) {
                $instance->add((string) $delegation);
            }
        }

        return $instance;
    }
}
