<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonnePhysique;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale\RepresentantLegal;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Delegations;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\Donnees;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Consentement;

class PdfTest extends TestCase
{
    private string $tempPdfPath;
    private Mandat $sampleMandat;

    protected function setUp(): void
    {
        $this->tempPdfPath = tempnam(sys_get_temp_dir(), 'test_mandat_') . '.pdf';

        // Créer un mandat d'exemple
        $mandant = new PersonnePhysique('Dupont', 'Jean', 'Address', 'email@test.com', '0123456789');
        $mandataire = new PersonneMorale(
            'Mandataire Corp',
            '123456789',
            'Mandataire Address',
            new RepresentantLegal('Legal', 'Rep', 'Manager', 'legal@corp.com', '0987654321')
        );

        $parties = new Parties($mandant, $mandataire);
        $donnees = new Donnees(true, false, true, 24);
        $pointsDeLivraison = new PointsDeLivraison();
        $delegations = new Delegations();

        $objet = new Objet($donnees, $pointsDeLivraison, $delegations);

        $consentement = new Consentement(
            new \DateTimeImmutable('2024-01-01T10:00:00+01:00'),
            new \DateTimeImmutable('2024-12-31T23:59:59+01:00')
        );

        $this->sampleMandat = new Mandat($parties, $objet, $consentement);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempPdfPath)) {
            unlink($this->tempPdfPath);
        }
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::__construct
     */
    public function testConstructor(): void
    {
        $pdf = new Pdf('/path/to/file.pdf');

        $this->assertSame('/path/to/file.pdf', $pdf->filePath);
    }

    /**     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::__construct
     */
    public function testConstructorWithRealFile(): void
    {
        // Test avec un fichier qui existe vraiment
        $pdf = new Pdf($this->tempPdfPath);

        $this->assertSame($this->tempPdfPath, $pdf->filePath);
    }

    /**     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::setMandat
     */
    public function testSetMandatOnEmptyFile(): void
    {
        // Créer un fichier PDF vide basique
        $basicPdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n184\n%%EOF";
        file_put_contents($this->tempPdfPath, $basicPdfContent);

        $pdf = new Pdf($this->tempPdfPath);

        $result = $pdf->setMandat($this->sampleMandat);

        $this->assertInstanceOf(Pdf::class, $result);

        $content = file_get_contents($this->tempPdfPath);
        $this->assertStringContainsString('anodeMetadonneesMandatAccesDonneesPDL:metadonnees', $content);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::setMandat
     */
    public function testSetMandatWithExistingXmpMeta(): void
    {
        // PDF avec XMP existant
        $pdfWithXmp = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<rdf:Description xmlns:dc=\"http://purl.org/dc/elements/1.1/\" rdf:about=\"\">\n<dc:title>Test PDF</dc:title>\n</rdf:Description>\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithXmp);

        $pdf = new Pdf($this->tempPdfPath);
        $pdf->setMandat($this->sampleMandat);

        $content = file_get_contents($this->tempPdfPath);
        $this->assertStringContainsString('anodeMetadonneesMandatAccesDonneesPDL:metadonnees', $content);
        $this->assertStringContainsString('<dc:title>Test PDF</dc:title>', $content);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::setMandat
     */
    public function testSetMandatWithExistingRdf(): void
    {
        // PDF avec structure RDF existante
        $pdfWithRdf = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<rdf:Description rdf:about=\"\"/>\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithRdf);

        $pdf = new Pdf($this->tempPdfPath);
        $pdf->setMandat($this->sampleMandat);

        $content = file_get_contents($this->tempPdfPath);
        $this->assertStringContainsString('anodeMetadonneesMandatAccesDonneesPDL:metadonnees', $content);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::setMandat
     */
    public function testSetMandatReplaceExistingPayload(): void
    {
        // PDF avec payload existant
        $pdfWithPayload = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<rdf:Description xmlns:anodeMetadonneesMandatAccesDonneesPDL=\"https://norme.anode.club/metadonnees-mandat-acces-donnees-pdl/v1/\" rdf:about=\"\">\n<anodeMetadonneesMandatAccesDonneesPDL:metadonnees>OLD_PAYLOAD</anodeMetadonneesMandatAccesDonneesPDL:metadonnees>\n</rdf:Description>\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithPayload);

        $pdf = new Pdf($this->tempPdfPath);
        $pdf->setMandat($this->sampleMandat);

        $content = file_get_contents($this->tempPdfPath);
        $this->assertStringNotContainsString('OLD_PAYLOAD', $content);
        $this->assertStringContainsString('anodeMetadonneesMandatAccesDonneesPDL:metadonnees', $content);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::setMandat
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::getMandat
     */
    public function testGetMandatSuccess(): void
    {
        // Créer un PDF et y écrire le mandat
        $basicPdfContent = "%PDF-1.4\nBasic content\n%%EOF";
        file_put_contents($this->tempPdfPath, $basicPdfContent);

        $pdf = new Pdf($this->tempPdfPath);
        $pdf->setMandat($this->sampleMandat);

        // Lire le mandat depuis le PDF
        $mandat = $pdf->getMandat();

        $this->assertInstanceOf(Mandat::class, $mandat);
        $this->assertInstanceOf(PersonnePhysique::class, $mandat->parties->mandant);
        $this->assertSame('Dupont', $mandat->parties->mandant->nom);
        $this->assertSame('Jean', $mandat->parties->mandant->prenom);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::getMandat
     */
    public function testGetMandatNoXmpMeta(): void
    {
        // PDF sans métadonnées XMP
        $pdfWithoutXmp = "%PDF-1.4\nNo XMP metadata here\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithoutXmp);

        $pdf = new Pdf($this->tempPdfPath);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Le bloc de métadonnées XMP est manquant dans le fichier PDF.');

        $pdf->getMandat();
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::getMandat
     */
    public function testGetMandatNoPayload(): void
    {
        // PDF avec XMP mais sans payload
        $pdfWithoutPayload = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<rdf:Description rdf:about=\"\">\n<dc:title>Test</dc:title>\n</rdf:Description>\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithoutPayload);

        $pdf = new Pdf($this->tempPdfPath);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("Le tag spécifique 'anodeMetadonneesMandatAccesDonneesPDL:metadonnees' est introuvable dans les métadonnées du PDF.");

        $pdf->getMandat();
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::getMandat
     */
    public function testGetMandatInvalidXml(): void
    {
        // PDF avec payload XML malformé
        $pdfWithBadXml = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<rdf:Description xmlns:anodeMetadonneesMandatAccesDonneesPDL=\"https://norme.anode.club/metadonnees-mandat-acces-donnees-pdl/v1/\" rdf:about=\"\">\n<anodeMetadonneesMandatAccesDonneesPDL:metadonnees><invalid<xml></anodeMetadonneesMandatAccesDonneesPDL:metadonnees>\n</rdf:Description>\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithBadXml);

        $pdf = new Pdf($this->tempPdfPath);

        $this->expectException(\DomainException::class);

        $pdf->getMandat();
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::setMandat
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::getMandat
     */
    public function testRoundTripComplexMandat(): void
    {
        // Créer un mandat complexe pour test round-trip complet
        $mandant = new PersonneMorale(
            'COMPLEX CORP & ASSOCIATES',
            '987654321',
            'Complex Address with "quotes" & <tags>',
            new RepresentantLegal(
                'Complex & Name',
                'Jean-François',
                'Président & Fondateur',
                'complex@email.com',
                '+33 1 23 45 67 89'
            )
        );

        $mandataire = new PersonnePhysique(
            'Individual',
            'Manager',
            'Individual Address',
            'individual@email.com',
            '01 98 76 54 32'
        );

        $parties = new Parties($mandant, $mandataire);

        $delegations = (new Delegations())
            ->add('COMPLEX_DELEGATION_1')
            ->add('COMPLEX_DELEGATION_2');

        $donnees = new Donnees(false, true, false, 36);
        $pointsDeLivraison = new PointsDeLivraison();

        $objet = new Objet($donnees, $pointsDeLivraison, $delegations);

        $consentement = new Consentement(
            new \DateTimeImmutable('2024-06-15T12:30:45+02:00'),
            new \DateTimeImmutable('2026-06-15T12:30:45+02:00')
        );

        $complexMandat = new Mandat($parties, $objet, $consentement);

        // Écrire et lire
        $basicPdfContent = "%PDF-1.4\nBasic content\n%%EOF";
        file_put_contents($this->tempPdfPath, $basicPdfContent);

        $pdf = new Pdf($this->tempPdfPath);
        $pdf->setMandat($complexMandat);
        $retrievedMandat = $pdf->getMandat();

        // Vérifications approfondies
        /** @var PersonneMorale $retrievedMandant */
        $retrievedMandant = $retrievedMandat->parties->mandant;
        $this->assertInstanceOf(PersonneMorale::class, $retrievedMandant);
        $this->assertSame('COMPLEX CORP & ASSOCIATES', $retrievedMandant->denominationSociale);
        $this->assertSame('Complex & Name', $retrievedMandant->representantLegal->nom);
        $this->assertSame('Jean-François', $retrievedMandant->representantLegal->prenom);

        /** @var PersonnePhysique $retrievedMandataire */
        $retrievedMandataire = $retrievedMandat->parties->mandataire;
        $this->assertInstanceOf(PersonnePhysique::class, $retrievedMandataire);
        $this->assertSame('Individual', $retrievedMandataire->nom);
        $this->assertSame('Manager', $retrievedMandataire->prenom);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::getContent
     */
    public function testGetContent(): void
    {
        $testContent = "%PDF-1.4\nTest PDF Content\n%%EOF";
        file_put_contents($this->tempPdfPath, $testContent);

        $pdf = new Pdf($this->tempPdfPath);
        $content = $pdf->getContent();

        $this->assertSame($testContent, $content);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::getMandat
     */
    public function testGetMandatWithEmptyPayload(): void
    {
        // PDF avec payload vide
        $pdfWithEmptyPayload = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<rdf:Description xmlns:anodeMetadonneesMandatAccesDonneesPDL=\"https://norme.anode.club/metadonnees-mandat-acces-donnees-pdl/v1/\" rdf:about=\"\">\n<anodeMetadonneesMandatAccesDonneesPDL:metadonnees></anodeMetadonneesMandatAccesDonneesPDL:metadonnees>\n</rdf:Description>\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithEmptyPayload);

        $pdf = new Pdf($this->tempPdfPath);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("Le contenu XML du mandat GRD est corrompu ou mal formé.");

        $pdf->getMandat();
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::getMandat
     */
    public function testGetMandatWithPayloadRootTag(): void
    {
        // PDF avec payload direct sans wrapper
        $payloadXml = '<mandat><parties><mandant><personnePhysique><nom>Test</nom><prenom>User</prenom><adressePostale>Address</adressePostale><adresseEmail>test@test.com</adresseEmail><numeroTelephone>123456</numeroTelephone></personnePhysique></mandant><mandataire><personneMorale><denominationSociale>Corp</denominationSociale><siren>123456789</siren><adresseSiegeSocial>Corp Address</adresseSiegeSocial><representantLegal><nom>Rep</nom><prenom>Legal</prenom><fonction>Manager</fonction><adresseEmail>rep@corp.com</adresseEmail><numeroTelephone>987654321</numeroTelephone></representantLegal></personneMorale></mandataire></parties><objet><donnees><courbeCharge>false</courbeCharge><technique>true</technique><usages>false</usages><dureeHistoriqueEnMois>12</dureeHistoriqueEnMois></donnees><pointsDeLivraison></pointsDeLivraison><delegations></delegations></objet><consentement><donneLe>2024-01-01T00:00:00+00:00</donneLe><expireLe>2024-12-31T23:59:59+00:00</expireLe></consentement></mandat>';
        $pdfWithPayloadRoot = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<rdf:Description xmlns:anodeMetadonneesMandatAccesDonneesPDL=\"https://norme.anode.club/metadonnees-mandat-acces-donnees-pdl/v1/\" rdf:about=\"\">\n<anodeMetadonneesMandatAccesDonneesPDL:metadonnees>$payloadXml</anodeMetadonneesMandatAccesDonneesPDL:metadonnees>\n</rdf:Description>\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithPayloadRoot);

        $pdf = new Pdf($this->tempPdfPath);
        $mandat = $pdf->getMandat();

        $this->assertInstanceOf(Mandat::class, $mandat);
        $this->assertSame('Test', $mandat->parties->mandant->nom);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::setMandat
     */
    public function testSetMandatWithXmpMetaButNoRdf(): void
    {
        // PDF avec xmpmeta mais sans RDF
        $pdfWithXmpOnly = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<!-- Commentaire -->\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithXmpOnly);

        $pdf = new Pdf($this->tempPdfPath);
        $result = $pdf->setMandat($this->sampleMandat);

        $this->assertInstanceOf(Pdf::class, $result);

        $content = file_get_contents($this->tempPdfPath);
        $this->assertStringContainsString('<rdf:RDF', $content);
        $this->assertStringContainsString('anodeMetadonneesMandatAccesDonneesPDL:metadonnees', $content);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::setMandat
     */
    public function testSetMandatWithRdfButNoDescription(): void
    {
        // PDF avec RDF mais sans Description contenant notre namespace
        $pdfWithRdfOnly = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<!-- Commentaire -->\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithRdfOnly);

        $pdf = new Pdf($this->tempPdfPath);
        $result = $pdf->setMandat($this->sampleMandat);

        $this->assertInstanceOf(Pdf::class, $result);

        $content = file_get_contents($this->tempPdfPath);
        $this->assertStringContainsString('rdf:Description', $content);
        $this->assertStringContainsString('anodeMetadonneesMandatAccesDonneesPDL', $content);
        $this->assertStringContainsString('anodeMetadonneesMandatAccesDonneesPDL:metadonnees', $content);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::setMandat
     */
    public function testSetMandatWithExistingDescription(): void
    {
        // PDF avec Description sans notre namespace -> ajout de nouvelle Description
        $pdfWithOtherDesc = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<rdf:Description xmlns:dc=\"http://purl.org/dc/elements/1.1/\" rdf:about=\"\">\n<dc:title>Existing Title</dc:title>\n</rdf:Description>\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithOtherDesc);

        $pdf = new Pdf($this->tempPdfPath);
        $result = $pdf->setMandat($this->sampleMandat);

        $this->assertInstanceOf(Pdf::class, $result);

        $content = file_get_contents($this->tempPdfPath);
        $this->assertStringContainsString('<dc:title>Existing Title</dc:title>', $content);
        $this->assertStringContainsString('anodeMetadonneesMandatAccesDonneesPDL:metadonnees', $content);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::setMandat
     */
    public function testSetMandatFormattingAndIndentation(): void
    {
        // Test que le formatage XML est appliqué
        $basicPdfContent = "%PDF-1.4\nBasic content\n%%EOF";
        file_put_contents($this->tempPdfPath, $basicPdfContent);

        $pdf = new Pdf($this->tempPdfPath);
        $pdf->setMandat($this->sampleMandat);

        $content = file_get_contents($this->tempPdfPath);

        // Vérifier que le XML est formaté (présence d'indentation)
        $this->assertStringContainsString('  <rdf:RDF', $content);
        $this->assertStringContainsString('    <rdf:Description', $content);
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::getMandat
     */
    public function testGetMandatWithMalformedMandatXml(): void
    {
        // PDF avec XML valide mais contenu mandat invalide
        $invalidMandatXml = '<mandat><parties></parties></mandat>'; // Parties vides = invalide
        $pdfWithInvalidMandat = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<rdf:Description xmlns:anodeMetadonneesMandatAccesDonneesPDL=\"https://norme.anode.club/metadonnees-mandat-acces-donnees-pdl/v1/\" rdf:about=\"\">\n<anodeMetadonneesMandatAccesDonneesPDL:metadonnees>$invalidMandatXml</anodeMetadonneesMandatAccesDonneesPDL:metadonnees>\n</rdf:Description>\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithInvalidMandat);

        $pdf = new Pdf($this->tempPdfPath);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Impossible de reconstruire l\'objet Mandat à partir du XML :');

        $pdf->getMandat();
    }

    /**
     * @covers AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Handler\Pdf::getMandat
     */
    public function testGetMandatWithPayloadWrapper(): void
    {
        // Test du cas où le XML a un wrapper payload vide
        $payloadXml = '<anodeMetadonneesMandatAccesDonneesPDL:payload></anodeMetadonneesMandatAccesDonneesPDL:payload>';
        $pdfWithEmptyPayloadWrapper = "%PDF-1.4\n<xmpmeta xmlns:x=\"adobe:ns:meta/\">\n<rdf:RDF xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">\n<rdf:Description xmlns:anodeMetadonneesMandatAccesDonneesPDL=\"https://norme.anode.club/metadonnees-mandat-acces-donnees-pdl/v1/\" rdf:about=\"\">\n<anodeMetadonneesMandatAccesDonneesPDL:metadonnees>$payloadXml</anodeMetadonneesMandatAccesDonneesPDL:metadonnees>\n</rdf:Description>\n</rdf:RDF>\n</xmpmeta>\n%%EOF";
        file_put_contents($this->tempPdfPath, $pdfWithEmptyPayloadWrapper);

        $pdf = new Pdf($this->tempPdfPath);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Le payload XML est vide.');

        $pdf->getMandat();
    }

    /**
     * Test avec différents encodages
     */
    public function testUtf8Encoding(): void
    {
        $mandant = new PersonnePhysique(
            'Émilie',
            'François-José',
            'Château des Étoiles, 75008 Paris',
            'émilie@château.fr',
            '+33 1 23 45 67 89'
        );

        $mandataire = new PersonneMorale(
            'SOCIÉTÉ ÉNERGÉTIQUE FRANÇAISE',
            '123456789',
            'Boulevard des Écuries, 69001 Lyon',
            new RepresentantLegal('Müller', 'André-José', 'Directeur Général', 'andre@société.fr', '04 12 34 56 78')
        );

        $parties = new Parties($mandant, $mandataire);
        $donnees = new Donnees(true, true, true, 12);
        $pointsDeLivraison = new PointsDeLivraison();
        $delegations = new Delegations();

        $objet = new Objet($donnees, $pointsDeLivraison, $delegations);

        $consentement = new Consentement(
            new \DateTimeImmutable('2024-01-01T00:00:00+01:00'),
            new \DateTimeImmutable('2024-12-31T23:59:59+01:00')
        );

        $utf8Mandat = new Mandat($parties, $objet, $consentement);

        // Test round-trip avec caractères UTF-8
        $basicPdfContent = "%PDF-1.4\nBasic content\n%%EOF";
        file_put_contents($this->tempPdfPath, $basicPdfContent);

        $pdf = new Pdf($this->tempPdfPath);
        $pdf->setMandat($utf8Mandat);
        $retrievedMandat = $pdf->getMandat();

        /** @var PersonnePhysique $retrievedMandant */
        $retrievedMandant = $retrievedMandat->parties->mandant;
        $this->assertSame('Émilie', $retrievedMandant->nom);
        $this->assertSame('François-José', $retrievedMandant->prenom);
        $this->assertSame('Château des Étoiles, 75008 Paris', $retrievedMandant->adressePostale);

        /** @var PersonneMorale $retrievedMandataire */
        $retrievedMandataire = $retrievedMandat->parties->mandataire;
        $this->assertSame('SOCIÉTÉ ÉNERGÉTIQUE FRANÇAISE', $retrievedMandataire->denominationSociale);
        $this->assertSame('Müller', $retrievedMandataire->representantLegal->nom);
    }
}
