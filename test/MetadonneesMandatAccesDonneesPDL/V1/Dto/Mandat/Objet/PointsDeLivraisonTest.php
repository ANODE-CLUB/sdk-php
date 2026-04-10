<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Prm;
use Anode\MetadonneesMandatAccesDonneesPDL\V1\Dto\Mandat\Objet\PointsDeLivraison\Pce;

class PointsDeLivraisonTest extends TestCase
{
    public function testConstructor(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();

        $this->assertInstanceOf(PointsDeLivraison::class, $pointsDeLivraison);
    }

    public function testAddSinglePrm(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();
        $prm = new Prm('12345678901234');

        $result = $pointsDeLivraison->add($prm);

        // Test fluent interface
        $this->assertSame($pointsDeLivraison, $result);

        // Vérifier que le point a été ajouté
        $points = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $points[] = $point;
        }

        $this->assertCount(1, $points);
        $this->assertSame($prm, $points[0]);
    }

    public function testAddSinglePce(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();
        $pce = new Pce('GI123456');

        $pointsDeLivraison->add($pce);

        $points = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $points[] = $point;
        }

        $this->assertCount(1, $points);
        $this->assertSame($pce, $points[0]);
    }

    public function testAddMultiplePoints(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();
        $prm1 = new Prm('12345678901234');
        $prm2 = new Prm('56789012345678');
        $pce1 = new Pce('GI123456');
        $pce2 = new Pce('GI789012');

        $pointsDeLivraison->add($prm1)
                         ->add($pce1)
                         ->add($prm2)
                         ->add($pce2);

        $points = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $points[] = $point;
        }

        $this->assertCount(4, $points);
        $this->assertSame($prm1, $points[0]);
        $this->assertSame($pce1, $points[1]);
        $this->assertSame($prm2, $points[2]);
        $this->assertSame($pce2, $points[3]);
    }

    public function testAddDuplicates(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();
        $prm = new Prm('12345678901234');

        $pointsDeLivraison->add($prm)->add($prm); // Ajout du même objet

        $points = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $points[] = $point;
        }

        // Les doublons ne sont pas filtrés
        $this->assertCount(2, $points);
        $this->assertSame($prm, $points[0]);
        $this->assertSame($prm, $points[1]);
    }

    public function testIterateEmptyCollection(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();

        $points = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $points[] = $point;
        }

        $this->assertEmpty($points);
    }

    public function testIterateReturnsGenerator(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();
        $pointsDeLivraison->add(new Prm('12345678901234'));

        $generator = $pointsDeLivraison->iterate();

        $this->assertInstanceOf(\Generator::class, $generator);
    }

    public function testIterateMultipleTimes(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();
        $prm = new Prm('12345678901234');
        $pce = new Pce('GI123456');

        $pointsDeLivraison->add($prm)->add($pce);

        // Premier parcours
        $firstIteration = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $firstIteration[] = $point;
        }

        // Deuxième parcours
        $secondIteration = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $secondIteration[] = $point;
        }

        $this->assertSame($firstIteration, $secondIteration);
        $this->assertCount(2, $firstIteration);
    }

    public function testBuildXmlEmpty(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();

        $xml = $pointsDeLivraison->buildXml();

        $this->assertStringContainsString('<pointsDeLivraison>', $xml);
        $this->assertStringContainsString('</pointsDeLivraison>', $xml);
        $this->assertStringNotContainsString('<pointDeLivraison>', $xml);
    }

    public function testBuildXmlWithPrmOnly(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();
        $pointsDeLivraison->add(new Prm('12345678901234'));

        $xml = $pointsDeLivraison->buildXml();

        $this->assertStringContainsString('<pointsDeLivraison>', $xml);
        $this->assertStringContainsString('<pointDeLivraison>', $xml);
        $this->assertStringContainsString('<prm>12345678901234</prm>', $xml);
        $this->assertStringContainsString('</pointDeLivraison>', $xml);
        $this->assertStringContainsString('</pointsDeLivraison>', $xml);
        $this->assertStringNotContainsString('<pce>', $xml);
    }

    public function testBuildXmlWithPceOnly(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();
        $pointsDeLivraison->add(new Pce('GI123456'));

        $xml = $pointsDeLivraison->buildXml();

        $this->assertStringContainsString('<pointsDeLivraison>', $xml);
        $this->assertStringContainsString('<pointDeLivraison>', $xml);
        $this->assertStringContainsString('<pce>GI123456</pce>', $xml);
        $this->assertStringContainsString('</pointDeLivraison>', $xml);
        $this->assertStringContainsString('</pointsDeLivraison>', $xml);
        $this->assertStringNotContainsString('<prm>', $xml);
    }

    public function testBuildXmlWithMixedPoints(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();
        $pointsDeLivraison->add(new Prm('12345678901234'))
                         ->add(new Pce('GI123456'))
                         ->add(new Prm('56789012345678'));

        $xml = $pointsDeLivraison->buildXml();

        $this->assertStringContainsString('<pointsDeLivraison>', $xml);
        $this->assertStringContainsString('<prm>12345678901234</prm>', $xml);
        $this->assertStringContainsString('<pce>GI123456</pce>', $xml);
        $this->assertStringContainsString('<prm>56789012345678</prm>', $xml);

        // Compter le nombre de balises pointDeLivraison
        $this->assertSame(3, substr_count($xml, '<pointDeLivraison>'));
        $this->assertSame(3, substr_count($xml, '</pointDeLivraison>'));
    }

    public function testMakeFromXmlEmpty(): void
    {
        $xmlString = '
        <pointsDeLivraison>
        </pointsDeLivraison>';

        $xml = simplexml_load_string($xmlString);
        $pointsDeLivraison = PointsDeLivraison::makeFromXml($xml);

        $points = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $points[] = $point;
        }

        $this->assertEmpty($points);
    }

    public function testMakeFromXmlWithPrmOnly(): void
    {
        $xmlString = '
        <pointsDeLivraison>
            <pointDeLivraison>
                <prm>12345678901234</prm>
            </pointDeLivraison>
        </pointsDeLivraison>';

        $xml = simplexml_load_string($xmlString);
        $pointsDeLivraison = PointsDeLivraison::makeFromXml($xml);

        $points = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $points[] = $point;
        }

        $this->assertCount(1, $points);
        $this->assertInstanceOf(Prm::class, $points[0]);
        $this->assertSame('12345678901234', $points[0]->valeur);
    }

    public function testMakeFromXmlWithPceOnly(): void
    {
        $xmlString = '
        <pointsDeLivraison>
            <pointDeLivraison>
                <pce>GI123456</pce>
            </pointDeLivraison>
        </pointsDeLivraison>';

        $xml = simplexml_load_string($xmlString);
        $pointsDeLivraison = PointsDeLivraison::makeFromXml($xml);

        $points = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $points[] = $point;
        }

        $this->assertCount(1, $points);
        $this->assertInstanceOf(Pce::class, $points[0]);
        $this->assertSame('GI123456', $points[0]->valeur);
    }

    public function testMakeFromXmlWithMixedPoints(): void
    {
        $xmlString = '
        <pointsDeLivraison>
            <pointDeLivraison>
                <prm>12345678901234</prm>
            </pointDeLivraison>
            <pointDeLivraison>
                <pce>GI123456</pce>
            </pointDeLivraison>
            <pointDeLivraison>
                <prm>56789012345678</prm>
            </pointDeLivraison>
        </pointsDeLivraison>';

        $xml = simplexml_load_string($xmlString);
        $pointsDeLivraison = PointsDeLivraison::makeFromXml($xml);

        $points = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $points[] = $point;
        }

        $this->assertCount(3, $points);

        $this->assertInstanceOf(Prm::class, $points[0]);
        $this->assertSame('12345678901234', $points[0]->valeur);

        $this->assertInstanceOf(Pce::class, $points[1]);
        $this->assertSame('GI123456', $points[1]->valeur);

        $this->assertInstanceOf(Prm::class, $points[2]);
        $this->assertSame('56789012345678', $points[2]->valeur);
    }

    public function testMakeFromXmlWithBothPrmAndPceInSamePoint(): void
    {
        // Edge case: point avec à la fois PRM et PCE
        $xmlString = '
        <pointsDeLivraison>
            <pointDeLivraison>
                <prm>12345678901234</prm>
                <pce>GI123456</pce>
            </pointDeLivraison>
        </pointsDeLivraison>';

        $xml = simplexml_load_string($xmlString);
        $pointsDeLivraison = PointsDeLivraison::makeFromXml($xml);

        $points = [];
        foreach ($pointsDeLivraison->iterate() as $point) {
            $points[] = $point;
        }

        // Seul le PCE devrait être ajouté (traité en dernier dans le code)
        $this->assertCount(1, $points);
        $this->assertInstanceOf(Pce::class, $points[0]);
        $this->assertSame('GI123456', $points[0]->valeur);
    }

    public function testRoundTripSerialization(): void
    {
        $originalPoints = new PointsDeLivraison();
        $prm1 = new Prm('12345678901234');
        $pce1 = new Pce('GI123456');
        $prm2 = new Prm('56789012345678');

        $originalPoints->add($prm1)->add($pce1)->add($prm2);

        // Sérialisation
        $xml = $originalPoints->buildXml();

        // Désérialisation
        $xmlElement = simplexml_load_string($xml);
        $reconstructedPoints = PointsDeLivraison::makeFromXml($xmlElement);

        // Comparer les points
        $originalArray = [];
        foreach ($originalPoints->iterate() as $point) {
            $originalArray[] = $point;
        }

        $reconstructedArray = [];
        foreach ($reconstructedPoints->iterate() as $point) {
            $reconstructedArray[] = $point;
        }

        $this->assertCount(count($originalArray), $reconstructedArray);

        for ($i = 0; $i < count($originalArray); $i++) {
            $this->assertSame(get_class($originalArray[$i]), get_class($reconstructedArray[$i]));
            $this->assertSame($originalArray[$i]->valeur, $reconstructedArray[$i]->valeur);
        }
    }

    public function testPropertyInitialization(): void
    {
        // Test du bug potentiel: propriété $pointsDeLivraison non initialisée
        $pointsDeLivraison = new PointsDeLivraison();

        $this->assertTrue(property_exists($pointsDeLivraison, 'pointsDeLivraison'));

        // Après construction, la propriété pourrait être non initialisée
        // Mais l'accès via add() devrait fonctionner
        $pointsDeLivraison->add(new Prm('12345678901234'));

        // Vérifier que la propriété est maintenant initialisée
        $this->assertIsArray($pointsDeLivraison->pointsDeLivraison);
        $this->assertCount(1, $pointsDeLivraison->pointsDeLivraison);
    }

    public function testDirectPropertyAccess(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();
        $prm = new Prm('12345678901234');
        $pce = new Pce('GI123456');

        $pointsDeLivraison->add($prm)->add($pce);

        // Accès direct à la propriété publique
        $this->assertIsArray($pointsDeLivraison->pointsDeLivraison);
        $this->assertCount(2, $pointsDeLivraison->pointsDeLivraison);
        $this->assertSame($prm, $pointsDeLivraison->pointsDeLivraison[0]);
        $this->assertSame($pce, $pointsDeLivraison->pointsDeLivraison[1]);
    }

    public function testFluentInterface(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();

        $result1 = $pointsDeLivraison->add(new Prm('123'));
        $result2 = $result1->add(new Pce('456'));
        $result3 = $result2->add(new Prm('789'));

        $this->assertSame($pointsDeLivraison, $result1);
        $this->assertSame($pointsDeLivraison, $result2);
        $this->assertSame($pointsDeLivraison, $result3);
    }

    public function testLargeNumberOfPoints(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();

        // Ajouter un grand nombre de points
        for ($i = 0; $i < 500; $i++) {
            if ($i % 2 === 0) {
                $pointsDeLivraison->add(new Prm(str_pad($i, 14, '0', STR_PAD_LEFT)));
            } else {
                $pointsDeLivraison->add(new Pce("GI$i"));
            }
        }

        $count = 0;
        foreach ($pointsDeLivraison->iterate() as $point) {
            $count++;
        }

        $this->assertSame(500, $count);
        $this->assertCount(500, $pointsDeLivraison->pointsDeLivraison);
    }

    public function testNotReadonlyClass(): void
    {
        $pointsDeLivraison = new PointsDeLivraison();

        // Vérifier que cette classe n'est PAS readonly
        $reflection = new \ReflectionClass($pointsDeLivraison);
        $this->assertFalse($reflection->isReadOnly());
    }
}
