<?php
namespace WordPressPopularPosts\Container;

use WordPressPopularPosts\{ Image, I18N, Output, Settings, Themer, Translate, WordPressPopularPosts };
use WordPressPopularPosts\Admin\Admin;
use WordPressPopularPosts\Block\Widget\Widget as BlockWidget;
use WordPressPopularPosts\Front\Front;
use WordPressPopularPosts\Shortcode\ShortcodeLoader;
use WordPressPopularPosts\Rest\{ Controller, PostsEndpoint, TaxonomiesEndpoint, ThemesEndpoint, ThumbnailsEndpoint, ViewLoggerEndpoint, WidgetEndpoint };
use WordPressPopularPosts\Widget\Widget;

class WordPressPopularPostsConfiguration implements ContainerConfigurationInterface
{
    /**
     * Modifies the given dependency injection container.
     *
     * @since   5.0.0
     * @param   Container $container
     */
    public function modify(Container $container)
    {
        $container['admin_options'] = Settings::get('admin_options');
        $container['widget_options'] = Settings::get('widget_options');

        $container['i18n'] = $container->service(function(Container $container) {
            return new I18N($container['admin_options']);
        });

        $container['translate'] = $container->service(function(Container $container) {
            return new Translate(); //phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction,WordPress.WP.I18n.MissingArgText -- We're using namespaces, it's fine
        });

        $container['image'] = $container->service(function(Container $container) {
            return new Image($container['admin_options']);
        });

        $container['themer'] = $container->service(function(Container $container) {
            return new Themer();
        });

        $container['output'] = $container->service(function(Container $container) {
            return new Output(
                $container['widget_options'],
                $container['admin_options'],
                $container['image'],
                $container['translate'],
                $container['themer']
            );
        });

        $container['widget'] = $container->service(function(Container $container) {
            return new Widget(
                $container['widget_options'],
                $container['admin_options'],
                $container['output'],
                $container['image'],
                $container['translate'],
                $container['themer']
            );
        });

        $container['block_widget'] = $container->service(function(Container $container) {
            return new BlockWidget(
                $container['admin_options'],
                $container['output'],
                $container['image'],
                $container['translate'],
                $container['themer']
            );
        });

        $container['posts_endpoint'] = $container->service(function(Container $container) {
            return new PostsEndpoint(
                $container['admin_options'],
                $container['translate']
            );
        });

        $container['view_logger_endpoint'] = $container->service(function(Container $container) {
            return new ViewLoggerEndpoint(
                $container['admin_options'],
                $container['translate']
            );
        });

        $container['taxonomies_endpoint'] = $container->service(function(Container $container) {
            return new TaxonomiesEndpoint(
                $container['admin_options'],
                $container['translate']
            );
        });

        $container['themes_endpoint'] = $container->service(function(Container $container) {
            return new ThemesEndpoint(
                $container['admin_options'],
                $container['translate'],
                $container['themer']
            );
        });

        $container['thumbnails_endpoint'] = $container->service(function(Container $container) {
            return new ThumbnailsEndpoint(
                $container['admin_options'],
                $container['translate']
            );
        });

        $container['widget_endpoint'] = $container->service(function(Container $container) {
            return new WidgetEndpoint(
                $container['admin_options'],
                $container['translate'],
                $container['output']
            );
        });

        $container['rest'] = $container->service(function(Container $container) {
            return new Controller(
                $container['posts_endpoint'],
                $container['view_logger_endpoint'],
                $container['widget_endpoint'],
                $container['themes_endpoint'],
                $container['thumbnails_endpoint'],
                $container['taxonomies_endpoint']
            );
        });

        $container['admin'] = $container->service(function(Container $container) {
            return new Admin(
                $container['admin_options'],
                $container['image']
            );
        });

        $container['front'] = $container->service(function(Container $container) {
            return new Front(
                $container['admin_options'],
                $container['translate']
            );
        });

        $container['shortcode_loader'] = $container->service(function(Container $container) {
            return new ShortcodeLoader(
                $container['admin_options'],
                $container['output']
            );
        });

        $container['wpp'] = $container->service(function(Container $container) {
            return new WordPressPopularPosts(
                $container['i18n'],
                $container['rest'],
                $container['admin'],
                $container['front'],
                $container['widget'],
                $container['block_widget'],
                $container['shortcode_loader']
            );
        });
    }
}
