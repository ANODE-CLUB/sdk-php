<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonnePhysique;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale\RepresentantLegal;

class PartieTest extends TestCase
{
    public function testPartieAbstractClass(): void
    {
        $reflection = new \ReflectionClass(Partie::class);

        $this->assertTrue($reflection->isAbstract());
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testPartieAbstractMethod(): void
    {
        $reflection = new \ReflectionClass(Partie::class);

        $this->assertTrue($reflection->hasMethod('buildXml'));

        $method = $reflection->getMethod('buildXml');
        $this->assertTrue($method->isAbstract());
        $this->assertTrue($method->isPublic());
        $this->assertSame('string', $method->getReturnType()->getName());
    }

    public function testPartieCannotBeInstantiated(): void
    {
        $this->expectException(\Error::class);

        new Partie(); // Should fail because it's abstract
    }

    public function testPersonnePhysiqueExtendsAbstractPartie(): void
    {
        $reflection = new \ReflectionClass(PersonnePhysique::class);

        $this->assertTrue($reflection->isSubclassOf(Partie::class));
        $this->assertFalse($reflection->isAbstract());
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testPersonneMoraleExtendsAbstractPartie(): void
    {
        $reflection = new \ReflectionClass(PersonneMorale::class);

        $this->assertTrue($reflection->isSubclassOf(Partie::class));
        $this->assertFalse($reflection->isAbstract());
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testPersonnePhysiqueImplementsBuildXml(): void
    {
        $partie = new PersonnePhysique(
            'Dupont',
            'Jean',
            'Address',
            'email@test.com',
            '0123456789'
        );

        $this->assertInstanceOf(Partie::class, $partie);

        // Vérifier que buildXml() est implémentée et retourne une string
        $xml = $partie->buildXml();
        $this->assertIsString($xml);
        $this->assertStringContainsString('<personnePhysique>', $xml);
    }

    public function testPersonneMoraleImplementsBuildXml(): void
    {
        $representant = new RepresentantLegal('Nom', 'Prenom', 'Function', 'email@test.com', '0123456789');

        $partie = new PersonneMorale('ACME', '123456789', 'Address', $representant);

        $this->assertInstanceOf(Partie::class, $partie);

        // Vérifier que buildXml() est implémentée et retourne une string
        $xml = $partie->buildXml();
        $this->assertIsString($xml);
        $this->assertStringContainsString('<personneMorale>', $xml);
    }

    public function testPolymorphism(): void
    {
        $personnePhysique = new PersonnePhysique(
            'Dupont',
            'Jean',
            'Address',
            'email@test.com',
            '0123456789'
        );

        $representant = new RepresentantLegal('Nom', 'Prenom', 'Function', 'email@test.com', '0123456789');
        $personneMorale = new PersonneMorale('ACME', '123456789', 'Address', $representant);

        $parties = [$personnePhysique, $personneMorale];

        foreach ($parties as $partie) {
            $this->assertInstanceOf(Partie::class, $partie);
            $this->assertIsString($partie->buildXml());
        }
    }

    public function testPersonnePhysiqueXmlSerialization(): void
    {
        $personne = new PersonnePhysique(
            'Dupont & Co',
            'Jean-François',
            'Address with "quotes" & <tags>',
            'jean.dupont@company.com',
            '+33 1 23 45 67 89'
        );

        $xml = $personne->buildXml();

        // Tester l'échappement XML
        $this->assertStringContainsString('<nom>Dupont &amp; Co</nom>', $xml);
        $this->assertStringContainsString('<prenom>Jean-François</prenom>', $xml);
        $this->assertStringContainsString('<adressePostale>Address with &quot;quotes&quot; &amp; &lt;tags&gt;</adressePostale>', $xml);
        $this->assertStringContainsString('<adresseEmail>jean.dupont@company.com</adresseEmail>', $xml);
        $this->assertStringContainsString('<numeroTelephone>+33 1 23 45 67 89</numeroTelephone>', $xml);
    }

    public function testPersonneMoraleXmlSerialization(): void
    {
        $representant = new RepresentantLegal(
            'Martin & Associates',
            'Claire-Marie',
            'Directrice Générale Déléguée',
            'claire.martin@corporate.fr',
            '+33 1 45 67 89 01'
        );

        $personne = new PersonneMorale(
            'ÉNERGIE & CHÂTEAU SARL',
            '123456789',
            'Château de Versailles & Co',
            $representant
        );

        $xml = $personne->buildXml();

        // Tester l'échappement XML
        $this->assertStringContainsString('<denominationSociale>ÉNERGIE &amp; CHÂTEAU SARL</denominationSociale>', $xml);
        $this->assertStringContainsString('<siren>123456789</siren>', $xml);
        $this->assertStringContainsString('<adresseSiegeSocial>Château de Versailles &amp; Co</adresseSiegeSocial>', $xml);

        // Représentant légal
        $this->assertStringContainsString('<representantLegal>', $xml);
        $this->assertStringContainsString('<nom>Martin &amp; Associates</nom>', $xml);
        $this->assertStringContainsString('<prenom>Claire-Marie</prenom>', $xml);
        $this->assertStringContainsString('<fonction>Directrice Générale Déléguée</fonction>', $xml);
    }

    public function testPersonnePhysiqueValidation(): void
    {
        // Test des propriétés obligatoires
        $personne = new PersonnePhysique(
            'Dupont',
            'Jean',
            'Address',
            'email@test.com',
            '0123456789'
        );

        $this->assertSame('Dupont', $personne->nom);
        $this->assertSame('Jean', $personne->prenom);
        $this->assertSame('Address', $personne->adressePostale);
        $this->assertSame('email@test.com', $personne->adresseEmail);
        $this->assertSame('0123456789', $personne->numeroTelephone);
    }

    public function testPersonneMoraleValidation(): void
    {
        $representant = new RepresentantLegal('Nom', 'Prenom', 'Function', 'email@test.com', '0123456789');

        $personne = new PersonneMorale(
            'ACME CORP',
            '123456789',
            'Corporate Address',
            $representant
        );

        $this->assertSame('ACME CORP', $personne->denominationSociale);
        $this->assertSame('123456789', $personne->siren);
        $this->assertSame('Corporate Address', $personne->adresseSiegeSocial);
        $this->assertInstanceOf(RepresentantLegal::class, $personne->representantLegal);
    }

    public function testRepresentantLegalValidation(): void
    {
        $representant = new RepresentantLegal(
            'Directeur',
            'Paul',
            'PDG',
            'paul@example.com',
            '0987654321'
        );

        $this->assertSame('Directeur', $representant->nom);
        $this->assertSame('Paul', $representant->prenom);
        $this->assertSame('PDG', $representant->fonction);
        $this->assertSame('paul@example.com', $representant->adresseEmail);
        $this->assertSame('0987654321', $representant->numeroTelephone);
    }

    public function testRepresentantLegalXmlSerialization(): void
    {
        $representant = new RepresentantLegal(
            'Représentant & Co',
            'Jean-Claude',
            'Président & Fondateur',
            'jc@company.com',
            '+33 (0) 1 23 45 67 89'
        );

        $xml = $representant->buildXml();

        $this->assertStringContainsString('<representantLegal>', $xml);
        $this->assertStringContainsString('<nom>Représentant &amp; Co</nom>', $xml);
        $this->assertStringContainsString('<prenom>Jean-Claude</prenom>', $xml);
        $this->assertStringContainsString('<fonction>Président &amp; Fondateur</fonction>', $xml);
        $this->assertStringContainsString('<adresseEmail>jc@company.com</adresseEmail>', $xml);
        $this->assertStringContainsString('<numeroTelephone>+33 (0) 1 23 45 67 89</numeroTelephone>', $xml);
        $this->assertStringContainsString('</representantLegal>', $xml);
    }

    public function testRepresentantLegalMakeFromXml(): void
    {
        $xmlString = '<representantLegal>
            <nom>Représentant Test</nom>
            <prenom>Jean-Claude</prenom>
            <fonction>Président</fonction>
            <adresseEmail>jc@company.com</adresseEmail>
            <numeroTelephone>+33 1 23 45 67 89</numeroTelephone>
        </representantLegal>';

        $xmlElement = simplexml_load_string($xmlString);
        $representant = RepresentantLegal::makeFromXml($xmlElement);

        $this->assertInstanceOf(RepresentantLegal::class, $representant);
        $this->assertSame('Représentant Test', $representant->nom);
        $this->assertSame('Jean-Claude', $representant->prenom);
        $this->assertSame('Président', $representant->fonction);
        $this->assertSame('jc@company.com', $representant->adresseEmail);
        $this->assertSame('+33 1 23 45 67 89', $representant->numeroTelephone);
    }

    public function testPersonnePhysiqueMakeFromXml(): void
    {
        $xmlString = '<personnePhysique>
            <nom>Test Physical</nom>
            <prenom>Jean-François</prenom>
            <adressePostale>123 rue Test</adressePostale>
            <adresseEmail>test@example.com</adresseEmail>
            <numeroTelephone>0123456789</numeroTelephone>
        </personnePhysique>';

        $xmlElement = simplexml_load_string($xmlString);
        $personne = PersonnePhysique::makeFromXml($xmlElement);

        $this->assertInstanceOf(PersonnePhysique::class, $personne);
        $this->assertSame('Test Physical', $personne->nom);
        $this->assertSame('Jean-François', $personne->prenom);
        $this->assertSame('123 rue Test', $personne->adressePostale);
        $this->assertSame('test@example.com', $personne->adresseEmail);
        $this->assertSame('0123456789', $personne->numeroTelephone);
    }

    public function testPersonneMoraleMakeFromXml(): void
    {
        $xmlString = '<personneMorale>
            <denominationSociale>Test Corp</denominationSociale>
            <siren>123456789</siren>
            <adresseSiegeSocial>456 avenue Test</adresseSiegeSocial>
            <representantLegal>
                <nom>Legal Rep</nom>
                <prenom>Paul</prenom>
                <fonction>Directeur</fonction>
                <adresseEmail>paul@test.com</adresseEmail>
                <numeroTelephone>0987654321</numeroTelephone>
            </representantLegal>
        </personneMorale>';

        $xmlElement = simplexml_load_string($xmlString);
        $personne = PersonneMorale::makeFromXml($xmlElement);

        $this->assertInstanceOf(PersonneMorale::class, $personne);
        $this->assertSame('Test Corp', $personne->denominationSociale);
        $this->assertSame('123456789', $personne->siren);
        $this->assertSame('456 avenue Test', $personne->adresseSiegeSocial);
        $this->assertInstanceOf(RepresentantLegal::class, $personne->representantLegal);
        $this->assertSame('Legal Rep', $personne->representantLegal->nom);
    }

    public function testPersonnePhysiqueValidationErrors(): void
    {
        // Test validation du nom
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom est obligatoire.');

        new PersonnePhysique('', 'Jean', 'Address', 'email@test.com', '0123456789');
    }

    public function testPersonnePhysiqueValidationPrenomError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prénom est obligatoire.');

        new PersonnePhysique('Dupont', '', 'Address', 'email@test.com', '0123456789');
    }

    public function testPersonnePhysiqueValidationAdresseError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'adresse postale est obligatoire.');

        new PersonnePhysique('Dupont', 'Jean', '', 'email@test.com', '0123456789');
    }

    public function testPersonnePhysiqueValidationEmailError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'adresse email est obligatoire.');

        new PersonnePhysique('Dupont', 'Jean', 'Address', '', '0123456789');
    }

    public function testPersonnePhysiqueValidationTelephoneError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le numéro de téléphone est obligatoire.');

        new PersonnePhysique('Dupont', 'Jean', 'Address', 'email@test.com', '');
    }

    public function testRepresentantLegalValidationErrors(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nom est obligatoire.');

        new RepresentantLegal('', 'Jean', 'Function', 'email@test.com', '0123456789');
    }

    /**
     * @covers Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie::makeFromXml
     */
    public function testPartieStaticMakeFromXmlPersonnePhysique(): void
    {
        $xml = simplexml_load_string('<partie><personnePhysique><nom>Dupont</nom><prenom>Jean</prenom><adressePostale>123 rue Test</adressePostale><adresseEmail>jean@example.com</adresseEmail><numeroTelephone>0123456789</numeroTelephone></personnePhysique></partie>');

        $partie = Partie::makeFromXml($xml);

        $this->assertInstanceOf(PersonnePhysique::class, $partie);
        $this->assertSame('Dupont', $partie->nom);
    }

    /**
     * @covers Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie::makeFromXml
     */
    public function testPartieStaticMakeFromXmlPersonneMorale(): void
    {
        $xml = simplexml_load_string('<partie><personneMorale><denominationSociale>ACME Corp</denominationSociale><siren>12345678901234</siren><adresseSiegeSocial>456 avenue Commerce</adresseSiegeSocial><representantLegal><nom>Martin</nom><prenom>Paul</prenom><fonction>CEO</fonction><adresseEmail>paul@acme.com</adresseEmail><numeroTelephone>0987654321</numeroTelephone></representantLegal></personneMorale></partie>');

        $partie = Partie::makeFromXml($xml);

        $this->assertInstanceOf(PersonneMorale::class, $partie);
        $this->assertSame('ACME Corp', $partie->denominationSociale);
    }

    /**
     * @covers Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie::makeFromXml
     */
    public function testPartieStaticMakeFromXmlInvalidType(): void
    {
        $xml = simplexml_load_string('<partie><autreType><nom>Invalid</nom></autreType></partie>');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le type de partie est invalide.');

        Partie::makeFromXml($xml);
    }

    /**
     * @covers Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale::buildXml
     */
    public function testPersonneMoraleBuildXmlWithNullRepresentant(): void
    {
        $personne = new PersonneMorale(
            'SOCIÉTÉ SANS REPRÉSENTANT',
            '987654321',
            'Adresse société',
            null  // representantLegal null
        );

        $xml = $personne->buildXml();

        $this->assertStringContainsString('<personneMorale>', $xml);
        $this->assertStringContainsString('<denominationSociale>SOCIÉTÉ SANS REPRÉSENTANT</denominationSociale>', $xml);
        $this->assertStringContainsString('<siren>987654321</siren>', $xml);
        $this->assertStringContainsString('<adresseSiegeSocial>Adresse société</adresseSiegeSocial>', $xml);
        $this->assertStringContainsString('<representantLegal />', $xml);
        $this->assertStringContainsString('</personneMorale>', $xml);
    }

    /**
     * @covers Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale\RepresentantLegal::buildXml
     */
    public function testRepresentantLegalBuildXmlDirectly(): void
    {
        $representant = new RepresentantLegal(
            'Nom Test',
            'Prénom Test',
            'Fonction Test',
            'test@email.com',
            '0123456789'
        );

        $xml = $representant->buildXml();

        $this->assertStringContainsString('<representantLegal>', $xml);
        $this->assertStringContainsString('<nom>Nom Test</nom>', $xml);
        $this->assertStringContainsString('<prenom>Prénom Test</prenom>', $xml);
        $this->assertStringContainsString('<fonction>Fonction Test</fonction>', $xml);
        $this->assertStringContainsString('<adresseEmail>test@email.com</adresseEmail>', $xml);
        $this->assertStringContainsString('<numeroTelephone>0123456789</numeroTelephone>', $xml);
        $this->assertStringContainsString('</representantLegal>', $xml);
    }

    /**
     * @covers Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Parties\Partie\PersonneMorale\RepresentantLegal::buildXml
     */
    public function testRepresentantLegalBuildXmlWithSpecialCharacters(): void
    {
        $representant = new RepresentantLegal(
            'Nom & Test <tag>',
            'Prénom "quoted"',
            'Fonction & Spéciale',
            'test@email.com',
            '+33 (0) 1 23 45 67 89'
        );

        $xml = $representant->buildXml();

        $this->assertStringContainsString('<nom>Nom &amp; Test &lt;tag&gt;</nom>', $xml);
        $this->assertStringContainsString('<prenom>Prénom &quot;quoted&quot;</prenom>', $xml);
        $this->assertStringContainsString('<fonction>Fonction &amp; Spéciale</fonction>', $xml);
    }

    /**
     * Tests de validation complémentaires pour RepresentantLegal
     */
    public function testRepresentantLegalValidationPrenomError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le prénom est obligatoire.');

        new RepresentantLegal('Nom', '', 'Function', 'email@test.com', '0123456789');
    }

    public function testRepresentantLegalValidationFonctionError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La fonction est obligatoire.');

        new RepresentantLegal('Nom', 'Prenom', '', 'email@test.com', '0123456789');
    }

    public function testRepresentantLegalValidationEmailError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'adresse email est obligatoire.');

        new RepresentantLegal('Nom', 'Prenom', 'Function', '', '0123456789');
    }

    public function testRepresentantLegalValidationTelephoneError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le numéro de téléphone est obligatoire.');

        new RepresentantLegal('Nom', 'Prenom', 'Function', 'email@test.com', '');
    }
}
