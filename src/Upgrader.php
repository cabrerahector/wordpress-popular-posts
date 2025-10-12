<?php
/**
 * Handles plugin upgrades.
 *
 * @link       https://cabrerahector.com
 * @since      7.4.0
 *
 * @package    WordPressPopularPosts
 */

namespace WordPressPopularPosts;

use WordPressPopularPosts\Activation\Activator;
use WordPressPopularPosts\Helper;

class Upgrader {

    /**
     * Registers class hooks.
     *
     * @since   7.4.0
     */
    public function hooks()
    {
        add_action('init', [$this, 'upgrade_check']);
    }

    /**
     * Checks whether an upgrade is required.
     *
     * @since   2.4.0
     */
    public function upgrade_check()
    {
        $this->upgrade_site();
    }

    /**
     * Upgrades single site.
     *
     * @since   4.0.7
     */
    private function upgrade_site()
    {
        // Get WPP version
        $wpp_ver = get_option('wpp_ver');

        if ( ! $wpp_ver ) {
            add_option('wpp_ver', WPP_VERSION);
        } elseif ( version_compare($wpp_ver, WPP_VERSION, '<') ) {
            $this->upgrade();
        }
    }

    /**
     * On plugin upgrade, performs a number of actions: update WPP database tables structures (if needed),
     * run the setup wizard (if needed), and some other checks.
     *
     * @since   2.4.0
     * @access  private
     * @global  object  $wpdb
     */
    private function upgrade()
    {
        $now = Helper::now();

        // Keep the upgrade process from running too many times
        $wpp_update = get_option('wpp_update');

        if ( $wpp_update ) {
            $from_time = strtotime($wpp_update);
            $to_time = strtotime($now);
            $difference_in_minutes = round(abs($to_time - $from_time)/60, 2);

            // Upgrade flag is still valid, abort
            if ( $difference_in_minutes <= 15 ) {
                return;
            }

            // Upgrade flag expired, delete it and continue
            delete_option('wpp_update');
        }

        global $wpdb;

        // Upgrade flag
        add_option('wpp_update', $now);

        // Set table name
        $prefix = $wpdb->prefix . 'popularposts';

        //phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange

        // Update data table structure and indexes
        $dataFields = $wpdb->get_results(
            $wpdb->prepare("SHOW FIELDS FROM %i;", "{$prefix}data")
        );

        foreach ( $dataFields as $column ) {
            if ( 'day' == $column->Field ) {
                $wpdb->query(
                    $wpdb->prepare("ALTER TABLE %i ALTER COLUMN day DROP DEFAULT;", "{$prefix}data")
                );
            }

            if ( 'last_viewed' == $column->Field ) {
                $wpdb->query(
                    $wpdb->prepare("ALTER TABLE %i ALTER COLUMN last_viewed DROP DEFAULT;", "{$prefix}data")
                );
            }
        }

        // Update summary table structure and indexes
        $summaryFields = $wpdb->get_results(
            $wpdb->prepare("SHOW FIELDS FROM %i;", "{$prefix}summary")
        );

        foreach ( $summaryFields as $column ) {
            if ( 'last_viewed' == $column->Field ) {
                $wpdb->query(
                    $wpdb->prepare("ALTER TABLE %i CHANGE last_viewed view_datetime datetime NOT NULL, ADD KEY view_datetime (view_datetime);", "{$prefix}summary")
                );
            }

            if ( 'view_date' == $column->Field ) {
                $wpdb->query(
                    $wpdb->prepare("ALTER TABLE %i ALTER COLUMN view_date DROP DEFAULT;", "{$prefix}summary")
                );
            }

            if ( 'view_datetime' == $column->Field ) {
                $wpdb->query(
                    $wpdb->prepare("ALTER TABLE %i ALTER COLUMN view_datetime DROP DEFAULT;", "{$prefix}summary")
                );
            }
        }

        $summaryIndexes = $wpdb->get_results(
            $wpdb->prepare("SHOW INDEX FROM %i;", "{$prefix}summary")
        );

        foreach( $summaryIndexes as $index ) {
            if ( 'ID_date' == $index->Key_name ) {
                $wpdb->query(
                    $wpdb->prepare("ALTER TABLE %i DROP INDEX ID_date;", "{$prefix}summary")
                );
            }

            if ( 'last_viewed' == $index->Key_name ) {
                $wpdb->query(
                    $wpdb->prepare("ALTER TABLE %i DROP INDEX last_viewed;", "{$prefix}summary")
                );
            }
        }

        $transientsIndexes = $wpdb->get_results(
            $wpdb->prepare("SHOW INDEX FROM %i;", "{$prefix}transients")
        );
        $transientsHasTKeyIndex = false;

        foreach( $transientsIndexes as $index ) {
            if ( 'tkey' == $index->Key_name ) {
                $transientsHasTKeyIndex = true;
                break;
            }
        }

        if ( ! $transientsHasTKeyIndex ) {
            $wpdb->query(
                $wpdb->prepare("TRUNCATE TABLE %i;", "{$prefix}transients")
            );
            $wpdb->query(
                $wpdb->prepare("ALTER TABLE %i ADD UNIQUE KEY tkey (tkey);", "{$prefix}transients")
            );
        }

        // Validate the structure of the tables, create missing tables / fields if necessary
        Activator::track_new_site();

        // Check storage engine
        $storage_engine_data = $wpdb->get_var(
            $wpdb->prepare("SELECT `ENGINE` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`=%s AND `TABLE_NAME`=%s;", $wpdb->dbname, "{$prefix}data")
        );

        if ( 'InnoDB' != $storage_engine_data ) {
            $wpdb->query(
                $wpdb->prepare("ALTER TABLE %i ENGINE=InnoDB;", "{$prefix}data")
            );
        }

        $storage_engine_summary = $wpdb->get_var(
            $wpdb->prepare("SELECT `ENGINE` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`=%s AND `TABLE_NAME`=%s;", $wpdb->dbname, "{$prefix}summary")
        );

        if ( 'InnoDB' != $storage_engine_summary ) {
            $wpdb->query(
                $wpdb->prepare("ALTER TABLE %i ENGINE=InnoDB;", "{$prefix}summary")
            );
        }

        //phpcs:enable

        // Update WPP version
        update_option('wpp_ver', WPP_VERSION);
        // Remove upgrade flag
        delete_option('wpp_update');
    }
}
