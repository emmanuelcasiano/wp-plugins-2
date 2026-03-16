<?php
// Variables available: $current_level, $post
// This file is a view — it only outputs HTML.
// Logic lives in PostMetaService.
?>
<p>
    <label for="opskel_reading_level">
        <?php esc_html_e('Select the reading level for this post:', 'oop-skeleton'); ?>
    </label>
</p>

<?php wp_nonce_field('opskel_save_meta', 'opskel_meta_nonce'); ?>

<select name="opskel_reading_level" id="opskel_reading_level" style="width:100%">
    <option value="">— Not set —</option>
    <?php
    $levels = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
    foreach ($levels as $value => $label) :
    ?>
        <option value="<?php echo esc_attr($value); ?>"
            <?php selected($current_level, $value); ?>>
            <?php echo esc_html($label); ?>
        </option>
    <?php endforeach; ?>
</select>