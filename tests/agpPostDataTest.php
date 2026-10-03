<?php

require_once __DIR__ . '/../admin/agp-admin.php';

class Agp_Post_Data_Test extends \WP_Mock\Tools\TestCase
{
    public function testPostDataConversionUsesTheSelectedPostType()
    {
        WP_Mock::userFunction('get_option')
            ->with('agp_automatic')
            ->andReturn('enabled');

        WP_Mock::userFunction('get_option')
            ->with('agp_automatic_post')
            ->andReturn(array('product'));

        WP_Mock::userFunction('get_option')
            ->with('agp_diphthongs')
            ->andReturn('disabled');

        WP_Mock::userFunction('sanitize_title')
            ->with('proion', '', 'save')
            ->andReturn('proion');

        $admin = new Agp_Admin('agp', '1.0.0', '/tmp/agp');
        $data = array('post_type' => 'product', 'post_name' => '');

        $result = $admin->greeklish_post_data(
            $data,
            array(),
            array('post_title' => 'Προϊόν'),
            false
        );

        $this->assertSame('proion', $result['post_name']);
    }

    public function testPostDataConversionDoesNotExpandEmptyPostTypeSelection()
    {
        WP_Mock::userFunction('get_option')
            ->with('agp_automatic')
            ->andReturn('enabled');

        WP_Mock::userFunction('get_option')
            ->with('agp_automatic_post')
            ->andReturn(array());

        $admin = new Agp_Admin('agp', '1.0.0', '/tmp/agp');
        $data = array('post_type' => 'product', 'post_name' => '');

        $result = $admin->greeklish_post_data(
            $data,
            array(),
            array('post_title' => 'Προϊόν'),
            false
        );

        $this->assertSame($data, $result);
    }

    public function testPostDataConversionDoesNotChangeSlugOnTitleOnlyUpdate()
    {
        WP_Mock::userFunction('get_option')
            ->with('agp_automatic')
            ->andReturn('enabled');

        WP_Mock::userFunction('get_option')
            ->with('agp_automatic_post')
            ->andReturn(array('product'));

        WP_Mock::userFunction('get_post_field')
            ->with('post_name', 123)
            ->andReturn('existing-slug');

        $admin = new Agp_Admin('agp', '1.0.0', '/tmp/agp');
        $data = array('post_type' => 'product', 'post_name' => 'existing-slug');

        $result = $admin->greeklish_post_data(
            $data,
            array('ID' => 123),
            array('post_title' => 'Νέος τίτλος'),
            true
        );

        $this->assertSame($data, $result);
    }

    public function testPostDataConversionTransliteratesExplicitSlugChange()
    {
        WP_Mock::userFunction('get_option')
            ->with('agp_automatic')
            ->andReturn('enabled');

        WP_Mock::userFunction('get_option')
            ->with('agp_automatic_post')
            ->andReturn(array('product'));

        WP_Mock::userFunction('get_option')
            ->with('agp_diphthongs')
            ->andReturn('disabled');

        WP_Mock::userFunction('get_post_field')
            ->with('post_name', 123)
            ->andReturn('existing-slug');

        WP_Mock::userFunction('sanitize_title')
            ->with('neo proion', '', 'save')
            ->andReturn('neo-proion');

        $admin = new Agp_Admin('agp', '1.0.0', '/tmp/agp');
        $data = array('post_type' => 'product', 'post_name' => 'raw-rest-slug');

        $result = $admin->greeklish_post_data(
            $data,
            array('ID' => 123),
            array('post_name' => 'νέο προϊόν'),
            true
        );

        $this->assertSame('neo-proion', $result['post_name']);
    }

    public function testPostDataConversionDoesNothingWhenDisabled()
    {
        WP_Mock::userFunction('get_option')
            ->with('agp_automatic')
            ->andReturn('disabled');

        $admin = new Agp_Admin('agp', '1.0.0', '/tmp/agp');
        $data = array('post_type' => 'product', 'post_name' => '');

        $result = $admin->greeklish_post_data(
            $data,
            array(),
            array('post_title' => 'Προϊόν'),
            false
        );

        $this->assertSame($data, $result);
    }

    public function testPostDataConversionDoesNothingForUnselectedPostType()
    {
        WP_Mock::userFunction('get_option')
            ->with('agp_automatic')
            ->andReturn('enabled');

        WP_Mock::userFunction('get_option')
            ->with('agp_automatic_post')
            ->andReturn(array('post'));

        $admin = new Agp_Admin('agp', '1.0.0', '/tmp/agp');
        $data = array('post_type' => 'product', 'post_name' => '');

        $result = $admin->greeklish_post_data(
            $data,
            array(),
            array('post_title' => 'Προϊόν'),
            false
        );

        $this->assertSame($data, $result);
    }
}
