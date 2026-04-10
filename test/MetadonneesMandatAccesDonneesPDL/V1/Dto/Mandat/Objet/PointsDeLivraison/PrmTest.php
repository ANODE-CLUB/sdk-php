<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use AnodeClub\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Prm;

class PrmTest extends TestCase
{
    public function testConstructor(): void
    {
        $prm = new Prm('12345678901234');

        $this->assertSame('12345678901234', $prm->valeur);
    }

    public function testConstructorWithEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La valeur est obligatoire.');

        new Prm('');
    }

    public function testConstructorWithVariousFormats(): void
    {
        $formats = [
            '12345678901234',     // Format standard 14 chiffres
            '1234567890123456',   // Plus long
            '123456789',          // Plus court
            'ABC1234567890',      // Avec lettres
            '12-34-56-78-90-12',  // Avec tirets
            '12.34.56.78.90.12',  // Avec points
            '12 34 56 78 90 12',  // Avec espaces
            '0000000000000000',   // Que des zéros
            '9999999999999999'    // Que des neuf
        ];

        foreach ($formats as $format) {
            $prm = new Prm($format);
            $this->assertSame($format, $prm->valeur);
        }
    }

    public function testConstructorWithSpecialCharacters(): void
    {
        $specialCases = [
            'PRM-123456789012',
            'FR_1234567890123',
            '123456789012#AB',
            '12345@domain.com',
            'PRM:123456789012'
        ];

        foreach ($specialCases as $specialCase) {
            $prm = new Prm($specialCase);
            $this->assertSame($specialCase, $prm->valeur);
        }
    }

    public function testConstructorWithUnicodeCharacters(): void
    {
        $prm = new Prm('PRM123456789éàç');

        $this->assertSame('PRM123456789éàç', $prm->valeur);
    }

    public function testToString(): void
    {
        $prm = new Prm('12345678901234');

        $this->assertSame('12345678901234', (string) $prm);
        $this->assertSame($prm->valeur, $prm->__toString());
    }

    public function testToStringWithEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La valeur est obligatoire.');

        new Prm('');
    }

    public function testToStringWithSpecialCharacters(): void
    {
        $prm = new Prm('PRM-123 & Co');

        $this->assertSame('PRM-123 & Co', (string) $prm);
    }

    public function testImplicitStringConversion(): void
    {
        $prm = new Prm('12345678901234');

        // Test conversion implicite en string dans différents contextes
        $concatenated = "PRM: " . $prm;
        $this->assertSame('PRM: 12345678901234', $concatenated);

        $interpolated = "Point de mesure: {$prm}";
        $this->assertSame('Point de mesure: 12345678901234', $interpolated);
    }

    public function testStringInterpolation(): void
    {
        $prm = new Prm('98765432109876');

        $message = "Le PRM {$prm} est valide";
        $this->assertSame('Le PRM 98765432109876 est valide', $message);
    }

    public function testArrayUsage(): void
    {
        $prms = [
            new Prm('12345678901234'),
            new Prm('56789012345678'),
            new Prm('90123456789012')
        ];

        $values = array_map(fn ($prm) => (string) $prm, $prms);
        $expected = ['12345678901234', '56789012345678', '90123456789012'];

        $this->assertSame($expected, $values);
    }

    public function testComparison(): void
    {
        $prm1 = new Prm('12345678901234');
        $prm2 = new Prm('12345678901234');
        $prm3 = new Prm('56789012345678');

        // Les objets sont différents même avec la même valeur
        $this->assertNotSame($prm1, $prm2);

        // Mais les valeurs sont identiques
        $this->assertSame($prm1->valeur, $prm2->valeur);
        $this->assertSame((string) $prm1, (string) $prm2);

        // Valeurs différentes
        $this->assertNotSame($prm1->valeur, $prm3->valeur);
        $this->assertNotSame((string) $prm1, (string) $prm3);
    }

    public function testImmutability(): void
    {
        $prm = new Prm('12345678901234');

        // Vérifier que la classe est readonly
        $this->assertTrue((new \ReflectionClass($prm))->isReadOnly());
    }

    public function testPublicProperties(): void
    {
        $prm = new Prm('12345678901234');

        // Vérifier que la propriété valeur est publique et accessible
        $reflection = new \ReflectionClass($prm);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $this->assertCount(1, $properties);

        $property = $properties[0];
        $this->assertSame('valeur', $property->getName());
        $this->assertTrue($property->isReadOnly());
    }

    public function testLongValue(): void
    {
        $longValue = str_repeat('1234567890', 100); // 1000 caractères
        $prm = new Prm($longValue);

        $this->assertSame($longValue, $prm->valeur);
        $this->assertSame($longValue, (string) $prm);
    }

    public function testNumericStringPreservation(): void
    {
        // Test avec des chaînes qui pourraient être interprétées comme des nombres
        $numericStrings = [
            '00000000000001',    // Avec zéros de tête
            '12345678901234',    // Nombre normal
            '01234567890123',    // Commence par zéro
            '12345678901234.0',  // Avec décimale
            '1.23456789e+13',    // Notation scientifique
            '+12345678901234',   // Avec signe plus
            '-12345678901234'    // Avec signe moins
        ];

        foreach ($numericStrings as $numericString) {
            $prm = new Prm($numericString);
            $this->assertSame($numericString, $prm->valeur);
            $this->assertSame($numericString, (string) $prm);
        }
    }

    public function testWhitespaceHandling(): void
    {
        $whitespaceValues = [
            ' 12345678901234 ',   // Espaces avant/après
            '12345678901234 ',    // Espace après
            ' 12345678901234',    // Espace avant
            "12345678901234\n",   // Retour ligne
            "12345678901234\t",   // Tabulation
            '12 34 56 78 90 12'   // Espaces internes
        ];

        foreach ($whitespaceValues as $value) {
            $prm = new Prm($value);
            // Les espaces sont préservés tels quels
            $this->assertSame($value, $prm->valeur);
            $this->assertSame($value, (string) $prm);
        }
    }

    public function testTypedProperty(): void
    {
        $prm = new Prm('12345678901234');

        // Vérifier le type de la propriété
        $reflection = new \ReflectionClass($prm);
        $property = $reflection->getProperty('valeur');

        $this->assertTrue($property->hasType());
        $this->assertSame('string', $property->getType()->getName());
    }
}
