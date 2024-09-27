<?php

use ProfilePress\Core\ContentProtection\ContentConditions;

$dbData = $restrictDataToExcludes

?>
<div class="pp-content-protection-excludes">
    <section id="ppContentProtectionContent">
        <div id="workflowConditions">
            <?php if (is_array($dbData) && ! empty($dbData)): $index = 0;
                $count                                               = count($dbData); ?>
                <?php foreach ($dbData as $facetListId => $facets) : ++$index; ?>
                    <?php ContentConditions::get_instance()->exempt_rules_group_row($facetListId, '', $facets); ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ( ! is_array($dbData) || empty($dbData)): ?>
                <?php ContentConditions::get_instance()->exempt_rules_group_row(
                    wp_generate_password(18, false),
                    wp_generate_password(18, false)
                ); ?>
            <?php endif; ?>
        </div>
    </section>
</div>
