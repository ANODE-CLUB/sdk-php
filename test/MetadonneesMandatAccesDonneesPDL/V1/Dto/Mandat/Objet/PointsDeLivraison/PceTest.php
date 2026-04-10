<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Pce;

class PceTest extends TestCase
{
    public function testConstructor(): void
    {
        $pce = new Pce('GI123456');

        $this->assertSame('GI123456', $pce->valeur);
    }

    public function testConstructorWithEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La valeur est obligatoire.');

        new Pce('');
    }

    public function testConstructorWithVariousFormats(): void
    {
        $formats = [
            'GI123456',           // Format standard
            'GI1234567890',       // Plus long
            'GI12',               // Plus court
            'AB123456',           // Préfixe différent
            '123456789',          // Sans préfixe
            'GI-123456',          // Avec tiret
            'GI.123456',          // Avec point
            'GI 123456',          // Avec espace
            'gi123456',           // Minuscules
            'GI000000',           // Avec zéros
            'GI999999'            // Avec neuf
        ];

        foreach ($formats as $format) {
            $pce = new Pce($format);
            $this->assertSame($format, $pce->valeur);
        }
    }

    public function testConstructorWithSpecialCharacters(): void
    {
        $specialCases = [
            'PCE-123456789',
            'FR_GI123456',
            'GI123456#AB',
            'GI123@domain.com',
            'PCE:GI123456',
            'GI123456-EXT',
            'GI123456/SUB'
        ];

        foreach ($specialCases as $specialCase) {
            $pce = new Pce($specialCase);
            $this->assertSame($specialCase, $pce->valeur);
        }
    }

    public function testConstructorWithUnicodeCharacters(): void
    {
        $pce = new Pce('GI123456éàç');

        $this->assertSame('GI123456éàç', $pce->valeur);
    }

    public function testToString(): void
    {
        $pce = new Pce('GI789012');

        $this->assertSame('GI789012', (string) $pce);
        $this->assertSame($pce->valeur, $pce->__toString());
    }

    public function testToStringWithEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La valeur est obligatoire.');

        new Pce('');
    }

    public function testToStringWithSpecialCharacters(): void
    {
        $pce = new Pce('PCE-123 & Co');

        $this->assertSame('PCE-123 & Co', (string) $pce);
    }

    public function testImplicitStringConversion(): void
    {
        $pce = new Pce('GI654321');

        // Test conversion implicite en string dans différents contextes
        $concatenated = "PCE: " . $pce;
        $this->assertSame('PCE: GI654321', $concatenated);

        $interpolated = "Point de comptage: {$pce}";
        $this->assertSame('Point de comptage: GI654321', $interpolated);
    }

    public function testStringInterpolation(): void
    {
        $pce = new Pce('GI987654');

        $message = "Le PCE {$pce} est valide";
        $this->assertSame('Le PCE GI987654 est valide', $message);
    }

    public function testArrayUsage(): void
    {
        $pces = [
            new Pce('GI123456'),
            new Pce('GI789012'),
            new Pce('GI345678')
        ];

        $values = array_map(fn ($pce) => (string) $pce, $pces);
        $expected = ['GI123456', 'GI789012', 'GI345678'];

        $this->assertSame($expected, $values);
    }

    public function testComparison(): void
    {
        $pce1 = new Pce('GI123456');
        $pce2 = new Pce('GI123456');
        $pce3 = new Pce('GI789012');

        // Les objets sont différents même avec la même valeur
        $this->assertNotSame($pce1, $pce2);

        // Mais les valeurs sont identiques
        $this->assertSame($pce1->valeur, $pce2->valeur);
        $this->assertSame((string) $pce1, (string) $pce2);

        // Valeurs différentes
        $this->assertNotSame($pce1->valeur, $pce3->valeur);
        $this->assertNotSame((string) $pce1, (string) $pce3);
    }

    public function testImmutability(): void
    {
        $pce = new Pce('GI123456');

        // Vérifier que la classe est readonly
        $this->assertTrue((new \ReflectionClass($pce))->isReadOnly());
    }

    public function testPublicProperties(): void
    {
        $pce = new Pce('GI123456');

        // Vérifier que la propriété valeur est publique et accessible
        $reflection = new \ReflectionClass($pce);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $this->assertCount(1, $properties);

        $property = $properties[0];
        $this->assertSame('valeur', $property->getName());
        $this->assertTrue($property->isReadOnly());
    }

    public function testLongValue(): void
    {
        $longValue = 'GI' . str_repeat('1234567890', 100); // Long identifiant
        $pce = new Pce($longValue);

        $this->assertSame($longValue, $pce->valeur);
        $this->assertSame($longValue, (string) $pce);
    }

    public function testNumericStringPreservation(): void
    {
        // Test avec des chaînes qui pourraient être interprétées comme des nombres
        $numericStrings = [
            '000000001',         // Avec zéros de tête
            '123456789',         // Nombre normal
            '012345678',         // Commence par zéro
            '123456.789',        // Avec décimale
            '1.23456e+6',        // Notation scientifique
            '+123456789',        // Avec signe plus
            '-123456789'         // Avec signe moins
        ];

        foreach ($numericStrings as $numericString) {
            $pce = new Pce($numericString);
            $this->assertSame($numericString, $pce->valeur);
            $this->assertSame($numericString, (string) $pce);
        }
    }

    public function testWhitespaceHandling(): void
    {
        $whitespaceValues = [
            ' GI123456 ',         // Espaces avant/après
            'GI123456 ',          // Espace après
            ' GI123456',          // Espace avant
            "GI123456\n",         // Retour ligne
            "GI123456\t",         // Tabulation
            'GI 12 34 56'         // Espaces internes
        ];

        foreach ($whitespaceValues as $value) {
            $pce = new Pce($value);
            // Les espaces sont préservés tels quels
            $this->assertSame($value, $pce->valeur);
            $this->assertSame($value, (string) $pce);
        }
    }

    public function testFrenchGasStandardFormats(): void
    {
        // Formats typiques des PCE français
        $frenchFormats = [
            'GI123456',          // Standard GrDF
            'GI1234567890123',   // Format long
            'GI000012345678',    // Avec zéros de remplissage
            'GI99999999999999'   // Valeur maximale
        ];

        foreach ($frenchFormats as $format) {
            $pce = new Pce($format);
            $this->assertSame($format, $pce->valeur);
        }
    }

    public function testCasePreservation(): void
    {
        // Test préservation de la casse
        $caseVariations = [
            'GI123456',    // Majuscules standards
            'gi123456',    // Minuscules
            'Gi123456',    // Mixte
            'gI123456'     // Mixte inverse
        ];

        foreach ($caseVariations as $variation) {
            $pce = new Pce($variation);
            $this->assertSame($variation, $pce->valeur);
            $this->assertSame($variation, (string) $pce);
        }
    }

    public function testRegionalVariations(): void
    {
        // Test variations régionales potentielles
        $regionalFormats = [
            'FR-GI123456',       // Avec préfixe pays
            'GI-FR-123456',      // Avec code pays interne
            'GI123456-75',       // Avec code département
            'GI123456-PARIS',    // Avec nom ville
            'GRDF-GI123456'      // Avec préfixe opérateur
        ];

        foreach ($regionalFormats as $format) {
            $pce = new Pce($format);
            $this->assertSame($format, $pce->valeur);
        }
    }

    public function testTypedProperty(): void
    {
        $pce = new Pce('GI123456');

        // Vérifier le type de la propriété
        $reflection = new \ReflectionClass($pce);
        $property = $reflection->getProperty('valeur');

        $this->assertTrue($property->hasType());
        $this->assertSame('string', $property->getType()->getName());
    }

    public function testComplexIdentifiers(): void
    {
        // Test avec identifiants complexes réalistes
        $complexIds = [
            'GI-2024-123456-TEMP',
            'PCE.FR.75.123456',
            'GI_TEMP_123456_2024',
            'GI:123456:BACKUP',
            'GI[123456]',
            'GI{123456}'
        ];

        foreach ($complexIds as $complexId) {
            $pce = new Pce($complexId);
            $this->assertSame($complexId, $pce->valeur);
            $this->assertSame($complexId, (string) $pce);
        }
    }
}
