<?php

if (!function_exists('cap_add_amp_actions')) {
    add_action('pre_amp_render_post', 'cap_add_amp_actions');

    function cap_add_amp_actions()
    {
        add_filter('amp_post_template_metadata', 'cap_update_amp_json_metadata', 10, 2);
        add_filter('amp_post_template_file', 'cap_set_amp_author_meta_template', 10, 3);
    }

    function cap_update_amp_json_metadata($metadata, $post)
    {
        $authors = get_post_authors($post->ID);

        $authors_json = [];
        foreach ($authors as $author) {
            $authors_json[] = [
                '@type' => 'Person',
                'name'  => $author->display_name,
            ];
        }
        $metadata['author'] = $authors_json;

        return $metadata;
    }

    function cap_set_amp_author_meta_template($file, $type, $post)
    {
        if ('meta-author' === $type) {
            $file = __DIR__ . '/amp/meta-author.php';
        }

        return $file;
    }
}
