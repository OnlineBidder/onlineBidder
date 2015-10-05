<?php
/**
 * Created by PhpStorm.
 * User: getju_000
 * Date: 07.05.14
 * Time: 13:46
 */

namespace getjump\Vk\Wrapper;


class Account extends BaseWrapper
{
    public function getAppPermissions()
    {
        return $this->vk->request('account.getAppPermissions')->response->data;
    }

    /**
     * @param $permissions
     * @param int|array $bitmask
     * @return int
     */
    public function validateRights($permissions, $bitmask = 0)
    {
        $valid = 0;
        if (is_array($bitmask)) {
            foreach ($bitmask as $bit) {
                $valid = $this->validateRights($permissions, $bit);
            }
        } else {
            $valid = $permissions & $bitmask;
        }
        return $valid;
    }
}
