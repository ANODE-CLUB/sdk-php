<?php

namespace AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet;

use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Prm;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Pce;

class PointsDeLivraison
{
    public array $pointsDeLivraison;

    public function __construct(
    ) {
        $this->pointsDeLivraison = [];
    }

    public function add(
        Prm|Pce $pointDeLivraison
    ): self {
        $this->pointsDeLivraison[] = $pointDeLivraison;

        return $this;
    }

    public function iterate(
    ): \Generator {
        foreach ($this->pointsDeLivraison as $pointDeLivraison) {
            yield $pointDeLivraison;
        }
    }

    public function buildXml(
    ): string {
        $xml = '<pointsDeLivraison>' . "\n";

        foreach ($this->iterate() as $pointDeLivraison) {
            $xml .= match(get_class($pointDeLivraison)) {
                Prm::class	=> '    <prm>' . htmlspecialchars((string)$pointDeLivraison, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</prm>' . "\n",
                Pce::class	=> '    <pce>' . htmlspecialchars((string)$pointDeLivraison, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</pce>' . "\n",
            };
        }

        $xml .= '</pointsDeLivraison>' . "\n";

        return $xml;
    }

    public static function makeFromXml(
        \SimpleXMLElement $pointsDeLivraison
    ): self {
        $instance = new self();

        if ($pointsDeLivraison) {
            foreach ($pointsDeLivraison->prm as $prm) {
                $instance->add(new Prm($prm));
            }
            foreach ($pointsDeLivraison->pce as $pce) {
                $instance->add(new Pce($pce));
            }
        }

        return $instance;
    }
}
