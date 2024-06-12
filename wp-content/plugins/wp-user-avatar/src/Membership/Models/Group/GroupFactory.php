<?php

namespace ProfilePress\Core\Membership\Models\Group;

use ProfilePress\Core\Membership\Models\FactoryInterface;
use ProfilePress\Core\Membership\Repositories\GroupRepository;

class GroupFactory implements FactoryInterface
{
    /**
     * @param $data
     *
     * @return GroupEntity
     */
    public static function make($data)
    {
        return new GroupEntity($data);
    }

    /**
     * @param $id
     *
     * @return GroupEntity
     */
    public static function fromId($id)
    {
        return GroupRepository::init()->retrieve(absint($id));
    }
}