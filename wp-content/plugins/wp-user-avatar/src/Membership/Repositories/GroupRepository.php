<?php

namespace ProfilePress\Core\Membership\Repositories;

use ProfilePress\Core\Base;
use ProfilePress\Core\Classes\PROFILEPRESS_sql;
use ProfilePress\Core\Membership\Models\Group\GroupEntity;
use ProfilePress\Core\Membership\Models\Group\GroupFactory;
use ProfilePress\Core\Membership\Models\ModelInterface;

class GroupRepository extends BaseRepository
{
    const DB_KEY = 'ppress_plan_group';

    /**
     * @param GroupEntity $data
     *
     * @return bool|int
     */
    public function add(ModelInterface $data)
    {
        $result = PROFILEPRESS_sql::add_meta_data(
            self::DB_KEY,
            [
                'name'                => $data->name,
                'plan_ids'            => $data->plan_ids,
                'plans_display_field' => $data->plans_display_field,
            ]
        );

        return ! $result ? false : $result;
    }

    /**
     * @param GroupEntity $data
     *
     * @return false
     */
    public function update(ModelInterface $data)
    {
        $result = PROFILEPRESS_sql::update_meta_value(
            $data->id,
            self::DB_KEY,
            [
                'name'                => $data->name,
                'plan_ids'            => $data->plan_ids,
                'plans_display_field' => $data->plans_display_field
            ]
        );

        return ! $result ? false : $data->id;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function delete($id)
    {
        return PROFILEPRESS_sql::delete_meta_data($id);
    }

    /**
     * @param $id
     *
     * @return GroupEntity
     */
    public function retrieve($id)
    {
        $result = PROFILEPRESS_sql::get_meta_value($id, self::DB_KEY);

        if ( ! $result || ! is_array($result)) {
            $result = [];
        } else {
            $result['id'] = intval($id);
        }

        return GroupFactory::make($result);
    }

    /**
     * @param int $limit
     * @param int $current_page
     * @param bool $count
     *
     * @return GroupEntity[]|array|int
     */
    public function retrieveAll($limit = 0, $current_page = 1, $order = 'DESC', $count = false)
    {
        $cache_key = sprintf('%s:%s:%s:%s', (string)$limit, (string)$current_page, $order, (string)$count);

        $cache = wp_cache_get($cache_key);


        if ($cache) return $cache;

        $table = Base::meta_data_db_table();

        $order_sort = in_array($order, ['DESC', 'ASC'], true) ? $order : 'DESC';

        $replacement = [self::DB_KEY];
        $sql         = "SELECT * FROM $table";
        if (true === $count) {
            $sql = "SELECT COUNT(*) FROM $table";
        }
        $sql .= " WHERE meta_key = %s";
        $sql .= " ORDER BY id $order_sort";

        if ($limit > 0) {
            $sql           .= " LIMIT %d";
            $replacement[] = $limit;
        }

        if ($current_page > 1) {
            $sql           .= "  OFFSET %d";
            $replacement[] = ($current_page - 1) * $limit;
        }

        $sql = $this->wpdb()->prepare($sql, $replacement);

        if (true === $count) {

            $result = (int)$this->wpdb()->get_var($sql);

            wp_cache_set($cache_key, $result, '', MINUTE_IN_SECONDS);

            return $result;
        }

        $result = $this->wpdb()->get_results($sql, 'ARRAY_A');

        if (is_array($result) && ! empty($result)) {

            $result = array_map(function ($data) {
                if (isset($data['meta_value'])) {
                    $data = GroupFactory::make(['id' => $data['id']] + unserialize($data['meta_value'], ['allowed_classes' => false]));
                }

                return $data;
            }, $result);

            wp_cache_set($cache_key, $result, '', MINUTE_IN_SECONDS);

            return $result;
        }

        return [];
    }
}