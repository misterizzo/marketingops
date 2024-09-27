(function ($) {

    var cr = {
        orRuleTemplate: wp.template('ppress-cr-or-rule'),
        orExemptTemplate: wp.template('ppress-cr-or-exempt'),
        andRuleTemplate: wp.template('ppress-cr-and-rule'),
        unlinkedAndRuleBadge: wp.template('ppress-cr-unlinked-and-rule-badge'),
        linkedAndRuleBadge: wp.template('ppress-cr-linked-and-rule-badge')
    };

    cr.uiBlock = function () {
        $('#ppress-content-protection-content').block({
            message: '<span class="spinner" style="visibility:visible;float:none;"></span>',
            css: {border: '0', backgroundColor: 'transparent'},
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: 0.9
            }
        });
    };

    cr.uiUnBlock = function () {
        $('#ppress-content-protection-content').unblock();
    };

    cr.uiExemptBlock = function () {
        $('#ppress-content-protection-excludes').block({
            message: '<span class="spinner" style="visibility:visible;float:none;"></span>',
            css: {border: '0', backgroundColor: 'transparent'},
            overlayCSS: {
                backgroundColor: '#fff',
                opacity: 0.9
            }
        });
    };

    cr.uiExemptUnBlock = function () {
        $('#ppress-content-protection-excludes').unblock();
    };

    cr.select2Init = function (isContentExempt = false) {
        if (isContentExempt) {
            $('.pp-content-protection-excludes .facetList .facet').each(function (index, element) {
                var selected_condition, select2options, fieldType, parent = $(element);

                $('.ppress-cr-select2', parent).each(function (index2, element2) {
                    if ($(element2).hasClass("select2-hidden-accessible") === false) {
                        selected_condition = $('.ppress-content-condition-exempt-rule-name', parent).val();
                        select2options = typeof ppress_cr_conditions[selected_condition]['field'] ? ppress_cr_conditions[selected_condition]['field'] : {};
                        fieldType = typeof select2options['type'] !== "undefined" ? select2options['type'] : '';
                        if (_.contains(['postselect', 'taxonomyselect'], fieldType)) {
                            select2options = _.extend(select2options, {
                                ajax: {
                                    url: ajaxurl,
                                    delay: 250,
                                    cache: true,
                                    data: function (params) {
                                        return {
                                            search: params.term,
                                            nonce: ppress_cr_nonce,
                                            action: 'ppress_cr_object_search',
                                            object_type: fieldType === 'postselect' ? 'post_type' : 'taxonomy',
                                            object_key: select2options.type === 'postselect' ? select2options.post_type : select2options.taxonomy
                                        };
                                    }
                                }
                            });
                        }

                        $(element2).select2(select2options);
                    }
                })
            });
        } else {
            $('.pp-content-protection-content-box .facetList .facet').each(function (index, element) {

                var selected_condition, select2options, fieldType, parent = $(element);

                $('.ppress-cr-select2', parent).each(function (index2, element2) {
                    if ($(element2).hasClass("select2-hidden-accessible") === false) {
                        selected_condition = $('.ppress-content-condition-rule-name', parent).val();
                        select2options = typeof ppress_cr_conditions[selected_condition]['field'] ? ppress_cr_conditions[selected_condition]['field'] : {};
                        fieldType = typeof select2options['type'] !== "undefined" ? select2options['type'] : '';
                        if (_.contains(['postselect', 'taxonomyselect'], fieldType)) {
                            select2options = _.extend(select2options, {
                                ajax: {
                                    url: ajaxurl,
                                    delay: 250,
                                    cache: true,
                                    data: function (params) {
                                        return {
                                            search: params.term,
                                            nonce: ppress_cr_nonce,
                                            action: 'ppress_cr_object_search',
                                            object_type: fieldType === 'postselect' ? 'post_type' : 'taxonomy',
                                            object_key: select2options.type === 'postselect' ? select2options.post_type : select2options.taxonomy
                                        };
                                    }
                                }
                            });
                        }

                        $(element2).select2(select2options);
                    }
                })
            });
        }
        $('#pp-cc-access-membership-plans').select2();

        $('#pp-cc-access-wp-users').select2({
            ajax: {
                url: ajaxurl,
                delay: 250,
                cache: true,
                data: function (params) {
                    return {
                        search: params.term,
                        nonce: ppress_cr_nonce,
                        action: 'ppress_cr_object_search',
                        object_type: 'wp_users',
                    };
                }
            },
            minimumInputLength: 2
        });
    };

    cr.generateUniqueID = function () {
        return Math.random().toString(36).substring(2) + Date.now().toString(36);
    };

    cr.addOrRule = function () {
        $(document).on('click', '#ppress-content-protection-content .addFacet', function () {
            $(this).trigger('blur');
            var parent = $(this).parents('.condAction');
            parent.find('.facetList').append(
                cr.orRuleTemplate({
                    facetListId: parent.data('facet-list'),
                    facetId: cr.generateUniqueID()
                })
            );
        });

        $(document).on('click', '#ppress-content-protection-excludes .addFacet', function () {
            $(this).trigger('blur');
            var parent = $(this).parents('.condAction');
            parent.find('.facetList').append(
                cr.orExemptTemplate({
                    facetListId: parent.data('facet-list'),
                    facetId: cr.generateUniqueID()
                })
            );
        });
    };

    cr.addAndRule = function () {
        $(document).on('click', '#ppress-content-protection-content .addCondition', function () {
            $(this).trigger('blur');
            $('#ppress-content-protection-content #workflowConditions').append(
                cr.andRuleTemplate({
                    facetListId: cr.generateUniqueID(),
                    facetId: cr.generateUniqueID()
                })
            );

            $(this).parents('.and').replaceWith(cr.unlinkedAndRuleBadge());
        });
    };

    cr.deleteOrRule = function () {
        $(document).on('click', '#ppress-content-protection-content .removeFacet', function () {
            $(this).trigger('blur');
            var andRuleWrapper = $(this).parents('.condAction').parent(),
                rule_wrapper = $(this).parents('.facetList').find('.facet');

            if (rule_wrapper.length === 1) {

                if ($('#ppress-content-protection-content .condAction').length === 1) {
                    rule_wrapper.find('.ppress-cr-rule-values').html('');
                    rule_wrapper.find('.ppress-content-condition-rule-name').val('');
                    return false;
                }

                andRuleWrapper.remove();

                $('p.and').eq(-1).replaceWith(cr.linkedAndRuleBadge());
                return;
            }

            $(this).parents('.facet').remove();
        });

        $(document).on('click', '#ppress-content-protection-excludes .removeFacet', function () {
            $(this).trigger('blur');
            var andRuleWrapper = $(this).parents('.condAction').parent(),
                rule_wrapper = $(this).parents('.facetList').find('.facet');

            if (rule_wrapper.length === 1) {

                if ($('#ppress-content-protection-content .condAction').length === 1) {
                    rule_wrapper.find('.ppress-cr-rule-values').html('');
                    rule_wrapper.find('.ppress-content-condition-exempt-rule-name').val('');
                    return false;
                }

                andRuleWrapper.remove();

                $('p.and').eq(-1).replaceWith(cr.linkedAndRuleBadge());
                return;
            }

            $(this).parents('.facet').remove();
        });
    };

    cr.getConditionValueField = function () {
        $(document).on('change', '.ppress-content-condition-rule-name', function () {

            var cr_rule_value_container = $(this).parents('.facet').find('.ppress-cr-rule-values'),
                selected_condition = this.value;

            cr_rule_value_container.html('');

            if (selected_condition === "") return false;

            var ajaxData = {
                    'action': 'ppress_content_condition_field',
                    'condition_id': this.value,
                    'nonce': ppress_cr_nonce
                },
                facetId = $(this).parents('.facet').data('facet'),
                facetListId = $(this).parents('.condAction').data('facet-list');

            if (typeof facetId !== "undefined") {
                ajaxData.facetId = facetId;
            }

            if (typeof facetListId !== "undefined") {
                ajaxData.facetListId = facetListId;
            }

            try {
                ajaxData.field_type = ppress_cr_conditions[selected_condition]['field']['type'];
            } catch (e) {
                return false;
            }

            cr.uiBlock();

            $.post(ajaxurl, ajaxData, function (response) {

                    if ('success' in response && response.success === true) {
                        cr_rule_value_container.append(response.data);
                        $(document).trigger('ppress_cr_field_added', selected_condition)
                    }

                    cr.uiUnBlock();
                }
            );
        });

        $(document).on('change', '.ppress-content-condition-exempt-rule-name', function () {

            var cr_rule_value_container = $(this).parents('.facet').find('.ppress-cr-rule-values'),
                selected_condition = this.value;

            cr_rule_value_container.html('');

            if (selected_condition === "") return false;

            var ajaxData = {
                    'action': 'ppress_exempt_content_condition_field',
                    'condition_id': this.value,
                    'nonce': ppress_cr_nonce
                },
                facetId = $(this).parents('.facet').data('facet'),
                facetListId = $(this).parents('.condAction').data('facet-list');

            if (typeof facetId !== "undefined") {
                ajaxData.facetId = facetId;
            }

            if (typeof facetListId !== "undefined") {
                ajaxData.facetListId = facetListId;
            }

            try {
                ajaxData.field_type = ppress_cr_conditions[selected_condition]['field']['type'];
            } catch (e) {
                return false;
            }

            cr.uiExemptBlock();

            $.post(ajaxurl, ajaxData, function (response) {

                    if ('success' in response && response.success === true) {
                        cr_rule_value_container.append(response.data);
                        $(document).trigger('ppress_cr_field_added_exempt', selected_condition)
                    }

                    cr.uiExemptUnBlock();
                }
            );
        })
    };

    cr.EventsListener = function () {
        $(document).on('ppress_cr_field_added', function () {
            cr.select2Init();
        });
        $(document).on('ppress_cr_field_added_exempt', function () {
            cr.select2Init(true);
        });
    };

    $(function () {
        cr.select2Init();
        cr.select2Init(true);
        cr.addOrRule();
        cr.addAndRule();
        cr.deleteOrRule();
        cr.getConditionValueField();
        cr.EventsListener();
    });

})(jQuery);