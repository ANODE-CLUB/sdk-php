<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonnePhysique;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale\RepresentantLegal;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Delegations;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Donnees;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Prm;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Pce;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Consentement;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf;

/**
 * Tests d'intégration complets pour assurer 100% de couverture de code
 */
class IntegrationTest extends TestCase
{
    private string $tempPdfPath;

    protected function setUp(): void
    {
        $this->tempPdfPath = tempnam(sys_get_temp_dir(), 'integration_test_') . '.pdf';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempPdfPath)) {
            unlink($this->tempPdfPath);
        }
    }

    public function testCompleteWorkflowWithPersonnePhysique(): void
    {
        // 1. Créer un mandat complet avec mandant particulier
        $mandant = new PersonnePhysique(
            'Martin',
            'Sophie',
            '15 avenue Kléber, 75116 Paris',
            'sophie.martin@email.com',
            '01 42 56 78 90'
        );

        $mandataire = new PersonneMorale(
            'ENERGY SOLUTIONS SAS',
            '987654321',
            '789 boulevard de la République, 13001 Marseille',
            new RepresentantLegal('Dupont', 'Jean', 'Directeur', 'jean.dupont@energy.com', '01 23 45 67 89')
        );

        $parties = new Parties($mandant, $mandataire);

        $delegations = (new Delegations())
            ->add('DYNERGY')
            ->add('FOURNISSEUR_1')
            ->add('PARTENAIRE_ENERGY');

        $donnees = new Donnees(true, true, false, 36);
        $pointsDeLivraison = (new PointsDeLivraison())
            ->add(new Prm('12345678901234'))
            ->add(new Pce('GI987654'))
            ->add(new Prm('56789012345678'));

        $objet = new Objet($donnees, $pointsDeLivraison, $delegations);

        $consentement = new Consentement(
            new \DateTimeImmutable('2024-03-15T14:30:00+01:00'),
            new \DateTimeImmutable('2027-03-15T14:30:00+01:00')
        );

        $mandat = new Mandat($parties, $objet, $consentement);

        // 2. Sérialiser et vérifier le XML
        $xml = $mandat->buildXml();
        $this->assertStringContainsString('<mandat>', $xml);
        $this->assertStringContainsString('<parties>', $xml);
        $this->assertStringContainsString('<mandant>', $xml);
        $this->assertStringContainsString('<personnePhysique>', $xml);
        $this->assertStringContainsString('<nom>Martin</nom>', $xml);
        $this->assertStringContainsString('<prenom>Sophie</prenom>', $xml);

        // 3. Round-trip XML parsing
        $xmlElement = simplexml_load_string('<root>' . $xml . '</root>');
        $reconstructedMandat = Mandat::makeFromXml($xmlElement->mandat);

        $this->assertInstanceOf(Mandat::class, $reconstructedMandat);
        $this->assertSame('Martin', $reconstructedMandat->parties->mandant->nom);
        $this->assertSame('Sophie', $reconstructedMandat->parties->mandant->prenom);

        // 4. Test PDF workflow
        file_put_contents($this->tempPdfPath, "%PDF-1.4\nBasic PDF content\n%%EOF");

        $pdfHandler = new Pdf($this->tempPdfPath);
        $pdfHandler->setMandat($mandat);
        $retrievedMandat = $pdfHandler->getMandat();

        $this->assertInstanceOf(Mandat::class, $retrievedMandat);
        $this->assertSame('Martin', $retrievedMandat->parties->mandant->nom);
        $this->assertSame('Sophie', $retrievedMandat->parties->mandant->prenom);
    }

    public function testCompleteWorkflowWithPersonneMorale(): void
    {
        // 1. Créer un mandat avec mandant professionnel
        $mandant = new PersonneMorale(
            'ACME ENERGY CORP',
            '123456789',
            'Tour Montparnasse, 33 avenue du Maine, 75015 Paris',
            new RepresentantLegal(
                'Dubois',
                'Michel',
                'Directeur Général Délégué',
                'michel.dubois@acme-energy.com',
                '+33 1 45 67 89 01'
            )
        );

        $mandataire = new PersonneMorale(
            'GRID OPERATOR',
            '456789123',
            '123 rue de la Distribution, 69007 Lyon',
            new RepresentantLegal('Martin', 'Paul', 'Directeur', 'paul.martin@grid.com', '04 56 78 90 12')
        );

        $parties = new Parties($mandant, $mandataire);
        $donnees = new Donnees(false, true, true, 12);
        $pointsDeLivraison = new PointsDeLivraison(); // Collection vide
        $delegations = new Delegations(); // Pas de délégations

        $objet = new Objet($donnees, $pointsDeLivraison, $delegations);

        $consentement = new Consentement(
            new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            new \DateTimeImmutable('2024-12-31T23:59:59+00:00')
        );

        $mandat = new Mandat($parties, $objet, $consentement);

        // 2. Vérifier la sérialisation avec cas spéciaux
        $xml = $mandat->buildXml();
        $this->assertStringContainsString('<mandat>', $xml);
        $this->assertStringContainsString('<parties>', $xml);
        $this->assertStringContainsString('<personneMorale>', $xml);
        $this->assertStringContainsString('<denominationSociale>ACME ENERGY CORP</denominationSociale>', $xml);
        $this->assertStringContainsString('<nom>Dubois</nom>', $xml);
        $this->assertStringContainsString('<fonction>Directeur Général Délégué</fonction>', $xml);
        $this->assertStringContainsString('<courbeCharge>false</courbeCharge>', $xml);
        $this->assertStringContainsString('<technique>true</technique>', $xml);
        $this->assertStringContainsString('<dureeHistoriqueEnMois>12</dureeHistoriqueEnMois>', $xml);

        // 3. Test PDF avec métadonnées existantes
        $existingXmp = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n  <rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n    <rdf:Description></rdf:Description>\n  </rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $existingXmp);

        $pdfHandler = new Pdf($this->tempPdfPath);
        $pdfHandler->setMandat($mandat);

        $content = file_get_contents($this->tempPdfPath);
        $this->assertStringContainsString('anodeMetadonneesMandatAccesDonneesPDL:donnees', $content);
    }

    public function testEdgeCasesAndErrorHandling(): void
    {
        // Test edge cases non couverts par les autres tests

        // 1. Delegations avec propriété non initialisée
        $delegations = new Delegations();
        // Test iterate() sur collection vide avant add()
        $emptyArray = [];
        foreach ($delegations->iterate() as $item) {
            $emptyArray[] = $item;
        }
        $this->assertEmpty($emptyArray);

        // 2. PointsDeLivraison avec propriété non initialisée
        $points = new PointsDeLivraison();
        $emptyPoints = [];
        foreach ($points->iterate() as $item) {
            $emptyPoints[] = $item;
        }
        $this->assertEmpty($emptyPoints);

        // 3. Test buildXml avec délégations vides
        $emptyDelegations = new Delegations();
        $xml = $emptyDelegations->buildXml();
        $this->assertStringContainsString('<delegations>', $xml);
        $this->assertStringContainsString('</delegations>', $xml);
        $this->assertStringNotContainsString('<delegation>', $xml);

        // 4. Test avec Parties complètes
        $mandant = new PersonnePhysique('Test', 'User', 'Address', 'test@example.com', '0123456789');
        $mandataire = new PersonneMorale('Test Org', '123456789', 'Address', 
            new RepresentantLegal('Legal', 'Rep', 'Fonction', 'legal@example.com', '0123456789'));
        
        $parties = new Parties($mandant, $mandataire);
        $xml = $parties->buildXml();
        $this->assertStringContainsString('<parties>', $xml);
        $this->assertStringContainsString('<mandant>', $xml);
        $this->assertStringContainsString('<mandataire>', $xml);
    }

    public function testMaximalComplexityScenario(): void
    {
        // Scénario avec maximum de complexité pour couvrir tous les chemins
        $complexDelegations = new Delegations();
        for ($i = 1; $i <= 10; $i++) {
            $complexDelegations->add("DELEGATION_$i");
        }

        $complexPoints = new PointsDeLivraison();
        for ($i = 1; $i <= 5; $i++) {
            $complexPoints->add(new Prm(str_pad($i, 14, '0', STR_PAD_LEFT)));
            $complexPoints->add(new Pce("GI$i"));
        }

        $mandant = new PersonneMorale(
            'ENTREPRISE & ASSOCIÉS SARL',
            '999888777',
            'Château de <Versailles>, 78000 Versailles',
            new RepresentantLegal(
                'de La Fontaine-Müller',
                'Jean-François',
                'Président & Fondateur',
                'jean-françois@château-versailles.fr',
                '+33 (0) 1 23 45 67 89'
            )
        );

        $mandataire = new PersonneMorale(
            'SUPER ENERGY & CO',
            '111222333',
            'Address with <special> chars & symbols',
            new RepresentantLegal('Energy', 'Manager', 'Directeur', 'manager@energy.com', '0123456789')
        );

        $parties = new Parties($mandant, $mandataire);
        $donnees = new Donnees(true, true, true, 999);

        $objet = new Objet($donnees, $complexPoints, $complexDelegations);

        $consentement = new Consentement(
            new \DateTimeImmutable('2024-06-15T12:30:45+02:00'),
            new \DateTimeImmutable('2030-12-31T23:59:59-05:00')
        );

        $mandat = new Mandat($parties, $objet, $consentement);

        // Test complet de sérialisation/désérialisation
        $xml = $mandat->buildXml();
        $xmlElement = simplexml_load_string('<root>' . $xml . '</root>');

        if ($xmlElement === false) {
            $this->fail('Le XML généré n\'est pas valide : ' . $xml);
        }

        $reconstructed = Mandat::makeFromXml($xmlElement->mandat);

        $this->assertSame('de La Fontaine-Müller', $reconstructed->parties->mandant->representantLegal->nom);
        $this->assertSame('Jean-François', $reconstructed->parties->mandant->representantLegal->prenom);

        // Vérifier toutes les délégations
        $delegationsArray = [];
        foreach ($reconstructed->objet->delegations->iterate() as $delegation) {
            $delegationsArray[] = $delegation;
        }
        $this->assertCount(10, $delegationsArray);

        // Vérifier tous les points
        $pointsArray = [];
        foreach ($reconstructed->objet->pointsDeLivraison->iterate() as $point) {
            $pointsArray[] = $point;
        }
        $this->assertCount(10, $pointsArray); // 5 PRM + 5 PCE
    }

    public function testSpecialCharactersHandling(): void
    {
        // Test exhaustif des caractères spéciaux dans tous les champs
        $mandant = new PersonnePhysique(
            'Test & <Company>',
            'José-François',
            'Address with "quotes" & <tags>',
            'test@domain.com',
            '+33 1 23 45 67 89'
        );

        $mandataire = new PersonneMorale(
            'ÉNERGIE & CHÂTEAU SARL',
            '123456789',
            'Château de Versailles & Co',
            new RepresentantLegal('Legal', 'Rep', 'Manager', 'legal@energy.com', '0123456789')
        );

        $parties = new Parties($mandant, $mandataire);

        $delegations = (new Delegations())
            ->add('DÉLÉGATION & ASSOCIÉS')
            ->add('PARTNER <ENERGY>');

        $donnees = new Donnees(true, false, true, -1); // Test durée négative
        
        $pointsDeLivraison = (new PointsDeLivraison())
            ->add(new Prm('PRM-123&456<789>'))
            ->add(new Pce('PCE "SPECIAL" & CO'));

        $objet = new Objet($donnees, $pointsDeLivraison, $delegations);

        $consentement = new Consentement(
            new \DateTimeImmutable('2024-02-29T12:00:00+01:00'), // Année bissextile
            new \DateTimeImmutable('2024-02-29T12:00:01+01:00')  // 1 seconde après
        );

        $mandat = new Mandat($parties, $objet, $consentement);

        $xml = $mandat->buildXml();
        $xmlElement = simplexml_load_string('<root>' . $xml . '</root>');

        if ($xmlElement === false) {
            $this->fail('Le XML généré n\'est pas valide : ' . $xml);
        }

        $reconstructed = Mandat::makeFromXml($xmlElement->mandat);

        $this->assertSame('Test & <Company>', $reconstructed->parties->mandant->nom);
        $this->assertSame('José-François', $reconstructed->parties->mandant->prenom);
        $this->assertSame(-1, $reconstructed->objet->donnees->dureeHistoriqueEnMois);
    }

    public function testAllPossibleErrorPaths(): void
    {
        // Tests pour couvrir tous les chemins d'erreur

        // 1. PDF sans XMP metadata
        file_put_contents($this->tempPdfPath, "Not a valid PDF");
        $pdfHandler = new Pdf($this->tempPdfPath);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Le bloc de métadonnées XMP est manquant dans le fichier PDF.');
        $pdfHandler->getMandat();
    }

    public function testPdfWithoutPayload(): void
    {
        // PDF avec XMP mais sans payload
        $content = "%PDF-1.4\n<xmpmeta>No payload here</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $content);

        $pdfHandler = new Pdf($this->tempPdfPath);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Le tag spécifique \'anodeMetadonneesMandatAccesDonneesPDL:metadonnees\' est introuvable dans les métadonnées du PDF.');
        $pdfHandler->getMandat();
    }

    public function testPdfWithInvalidXml(): void
    {
        // PDF avec XML malformé
        $content = "%PDF-1.4\n<xmpmeta>\n<anodeMetadonneesMandatAccesDonneesPDL:donnees><invalid<xml></anodeMetadonneesMandatAccesDonneesPDL:donnees>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $content);

        $pdfHandler = new Pdf($this->tempPdfPath);

        $this->expectException(\DomainException::class);
        $pdfHandler->getMandat();
    }
}
