<?php
require_once('../../../wp-load.php');

$terms = get_terms( array(
    'taxonomy' => 'activity_unit',
    'hide_empty' => false,
) );

echo "Term Debug List:\n";
foreach ( $terms as $term ) {
    echo "Name: " . $term->name . " | Slug: " . $term->slug . "\n";
}
