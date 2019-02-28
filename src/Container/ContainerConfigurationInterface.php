<?php
/**
 * Contract to modify the DIC.
 */

namespace WordPressPopularPosts\Container;

interface ContainerConfigurationInterface
{
    /**
     * Modifies the given dependency injection container.
     *
     * @since   5.0.0
     * @param   Container $container
     */
    public function modify(Container $container);
}
