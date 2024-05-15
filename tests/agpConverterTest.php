<?php

include './includes/agp-converter.php';

class Agp_Converter_Test extends \WP_Mock\Tools\TestCase
{

    public function testConvertSlugWithDiphthonsEnabled()
    {

        WP_Mock::userFunction('get_option')
            ->with('agp_diphthongs')
            ->andReturn('enabled');

        $classInstance = new Agp_Converter();

        $conversion_array = array(
            'Μεταγραφή Ελληνικών χαρακτήρων' => 'metagrafi ellinikon charaktiron',
            'Μαύρο πρόβατο' => 'mavro provato',
            'αυτό το καλοκαιρι' => 'afto to kalokairi',
            'τελευταία ευκαιρία' => 'teleftaia efkairia',
            'ευεξία και χαλάρωση' => 'evexia kai chalarosi',
            'Όλοι οι άνθρωποι γεννιούνται ελεύθεροι και ίσοι στην αξιοπρέπεια' => 'oloi oi anthropoi genniountai eleftheroi kai isoi stin axioprepeia'
        );

        foreach ($conversion_array as $original => $transliteration) {
            $this->assertSame($transliteration, $classInstance->convertSlug($original));
        }
    }

    public function testConvertSlugWithDiphthonsDisabled()
    {

        WP_Mock::userFunction('get_option')
            ->with('agp_diphthongs')
            ->andReturn('disabled');

        $classInstance = new Agp_Converter();

        $conversion_array = array(
            'Μεταγραφή Ελληνικών χαρακτήρων' => 'metagrafi ellinikon charaktiron',
            'Μαύρο πρόβατο' => 'mayro provato',
            'αυτό το καλοκαιρι' => 'ayto to kalokairi',
            'τελευταία ευκαιρία' => 'teleytaia eykairia',
            'ευεξία και χαλάρωση' => 'eyexia kai chalarosi',
            'Όλοι οι άνθρωποι γεννιούνται ελεύθεροι και ίσοι στην αξιοπρέπεια' => 'oloi oi anthropoi gennioyntai eleytheroi kai isoi stin axioprepeia'
        );

        foreach ($conversion_array as $original => $transliteration) {
            $this->assertSame($transliteration, $classInstance->convertSlug($original));
        }
    }

    public function testConvertSlugWithExpresionFilter()
    {

        WP_Mock::userFunction('get_option')
            ->with('agp_diphthongs')
            ->andReturn('disabled');

        WP_Mock::onFilter('agp_convert_expressions')
            ->with(Agp_Converter::getExpressions())
            ->reply(array('/[βΒ]/u' => 'g'));

        $classInstance = new Agp_Converter();

        $this->assertSame('gα', $classInstance->convertSlug('βα'));
    }
}
