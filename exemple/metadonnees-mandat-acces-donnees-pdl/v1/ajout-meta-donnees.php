<?php

require __DIR__ . '/../../../vendor/autoload.php';

use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonnePhysique;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale\RepresentantLegal;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Delegations;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Donnees;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Pce;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Prm;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Consentement;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf;

$mandat = new Mandat(
    new Parties(
        new PersonneMorale(
            'ACME',
            '123456789',
            '1 rue de la République, 69001 Lyon',
            new RepresentantLegal(
                'MARTIN',
                'Julien',
                'Président',
                'julien.martin@domain.ext',
                '+33123456789'
            )
        ),
        new PersonneMorale(
            'Wayne Enterprises',
            '987654321',
            '1 rue de Rivoli, 75001 Paris',
            null
        ),
    ),
    new Objet(
        new Donnees(
            true,
            true,
            true,
            24
        ),
        (new PointsDeLivraison())
            ->add(new Prm('12345678901234'))
            ->add(new Prm('98765432109876'))
            ->add(new Pce('GI123456')),
        (new Delegations())
            ->add('Fournisseurs d’électricité et de gaz naturel (liste fournie sur simple demande), pour l’obtention d’offres de fourniture.')
            ->add('Partenaires du Mandataire (liste fournie sur simple demande), pour des prestations spécifiques.')
    ),
    new Consentement(
        new \DateTimeImmutable('2024-01-01 12:34:56', new \DateTimeZone('Europe/Paris')),
        new \DateTimeImmutable('2024-12-31 23:59:59', new \DateTimeZone('Europe/Paris')),
    )
);

$pdfHandler = new Pdf(
    'mandat.pdf'
);
$pdfHandler->setMandat(
    $mandat
);
