<?php
/*
Plugin Name: WP-CLI Term Merge
Plugin URI: https://github.com/aucor/wp-cli-term-merge
Version: 1.0.0
Author: Aucor Oy
Author URI: https://github.com/aucor
Description: Merge two terms into one
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-cli-term-merge
*/

defined('ABSPATH') or die('Nope');

if (defined('WP_CLI') && WP_CLI) {

  class WP_CLI_Term_Merge {

    protected $updated;
    protected $ignored;

    /**
     * wp term-merge run --from=123 --to=321
     * wp term-merge run --from=123 --to=321 --skip-delete
     *
     * --post_type=page
     */
    public function run($args = array(), $assoc_args = array()) {

      if (!isset($assoc_args['from']) || !isset($assoc_args['to'])) {
        WP_CLI::error('Missing required args "from" and "to"');
      }

      $from_term = get_term(absint($assoc_args['from']));
      if (!($from_term instanceof WP_Term)) {
        WP_CLI::error('Invalid term in "from"');
      }

      $to_term = get_term(absint($assoc_args['to']));
      if (!($to_term instanceof WP_Term)) {
        WP_CLI::error('Invalid term in "to"');
      }

      $skip_delete = false;
      if (isset($assoc_args['skip-delete'])) {
        $skip_delete = true;
      }

      $count = 0;

      $query = new WP_Query([
        'post_type'       => 'any',
        'posts_per_page'  => -1,
        'post_status'     => 'any',
        'lang'            => '',
        'tax_query'       => [
          [
            'taxonomy' => $from_term->taxonomy,
            'field'    => 'term_id',
            'terms'    => [$from_term->term_id],
          ],
        ],
      ]);

      while ($query->have_posts()) : $query->the_post();

        // remove "from" term
        $from_terms = wp_get_post_terms(get_the_ID(), $from_term->taxonomy);
        $from_terms_ids = [];
        foreach ($from_terms as $term) {
          if ($term instanceof WP_Term && $term->term_id !== $from_term->term_id) {
            $from_terms_ids[] = $term->term_id;
          }
        }
        wp_set_post_terms(get_the_ID(), $from_terms_ids, $from_term->taxonomy, false);

        // insert "to" term
        $to_terms = wp_get_post_terms(get_the_ID(), $to_term->taxonomy);
        $to_terms_ids = [];
        foreach ($to_terms as $term) {
          if ($term instanceof WP_Term) {
            $to_terms_ids[] = $term->term_id;
          }
        }
        $to_terms_ids[] = $to_term->term_id;
        wp_set_post_terms(get_the_ID(), $to_terms_ids, $to_term->taxonomy, false);

        // update count
        $count++;

      endwhile;

      WP_CLI::success('Done: ' . $count . ' posts moved from #' . $from_term->term_id . ' (' . $from_term->name . ') to #' . $to_term->term_id . ' (' . $to_term->name . ')');

      // delete "from" term
      if (!$skip_delete) {
        wp_delete_term($from_term->term_id, $from_term->taxonomy);
        WP_CLI::success('Deleted term: #' . $from_term->term_id . ' (' . $from_term->name . ')');
      }

    }

  }

  WP_CLI::add_command('term-merge', 'WP_CLI_Term_Merge');

}
