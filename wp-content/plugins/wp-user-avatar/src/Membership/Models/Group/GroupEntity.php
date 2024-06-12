<?php

namespace ProfilePress\Core\Membership\Models\Group;

use ProfilePress\Core\Membership\Models\AbstractModel;
use ProfilePress\Core\Membership\Models\ModelInterface;
use ProfilePress\Core\Membership\Repositories\GroupRepository;

/**
 * @property int $id
 * @property string $name
 * @property string $plans_display_field
 * @property int[] $plan_ids
 */
class GroupEntity extends AbstractModel implements ModelInterface
{
    protected $id = 0;

    protected $name = '';

    protected $plan_ids = [];

    protected $plans_display_field = 'radio';

    public function __construct($data = [])
    {
        if (is_array($data) && ! empty($data)) {

            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return ! empty($this->id);
    }

    /**
     * @return int
     */
    public function get_id()
    {
        return absint($this->id);
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }

    public function get_plans_display_field()
    {
        return $this->plans_display_field;
    }

    /**
     * @return int[]
     */
    public function get_plan_ids()
    {
        $plan_ids = is_array($this->plan_ids) ? $this->plan_ids : [];

        return apply_filters('ppress_group_plan_ids', array_map('absint', $plan_ids), $this);
    }

    /**
     * @return int|null
     */
    public function get_default_plan_id()
    {
        return apply_filters('ppress_default_plan_id', ppress_var($this->get_plan_ids(), 0, false), $this);
    }

    /**
     * @return false|string
     */
    public function get_checkout_url()
    {
        $page_id = ppress_settings_by_key('checkout_page_id');

        if ( ! empty($page_id)) {

            return add_query_arg('group', absint($this->get_id()), get_permalink($page_id));
        }

        return false;
    }

    /**
     * @return false|int
     */
    public function save()
    {
        if ($this->id > 0) {

            $result = GroupRepository::init()->update($this);

            do_action('ppress_membership_update_group', $result, $this);

            return $result;
        }

        $result = GroupRepository::init()->add($this);

        do_action('ppress_membership_add_group', $result, $this);

        return $result;
    }
}