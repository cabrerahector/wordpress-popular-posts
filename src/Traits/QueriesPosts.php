<?php
namespace WordPressPopularPosts\Traits;

use WordPressPopularPosts\Query;

trait QueriesPosts
{
    /**
     * Query object.
     *
     * @since   6.0.0
     * @var     \WordPressPopularPosts\Query
     * @access  private
     */
    private $query;

    /**
     * Gets Query object from cache if it exists,
     * otherwise a new Query object will be
     * instantiated and returned.
     *
     * @since   6.0.0
     * @param   array
     * @return  Query
     */
    protected function maybe_query(array $params)
    {
        // Return cached results
        if ( $this->config['tools']['cache']['active'] ) {
            $key = 'wpp_' . md5(json_encode($params));
            $this->query = \WordPressPopularPosts\Cache::get($key);

            if ( false === $this->query ) {
                $this->query = new Query($params);

                $time_value = $this->config['tools']['cache']['interval']['value'];
                $time_unit = $this->config['tools']['cache']['interval']['time'];

                // No popular posts found, check again in 1 minute
                if ( ! $this->query->get_posts() ) {
                    $time_value = 1;
                    $time_unit = 'minute';
                }

                \WordPressPopularPosts\Cache::set(
                    $key,
                    $this->query,
                    $time_value,
                    $time_unit
                );
            }
        } // Get real-time popular posts
        else {
            $this->query = new Query($params);
        }

        return $this->query;
    }
}
