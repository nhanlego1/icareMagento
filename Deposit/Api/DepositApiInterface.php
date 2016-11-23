<?php
/**
 * Copyright (c) 2016
 * Created by: nhan_nguyen
 */

namespace Icare\Deposit\Api;

interface DepositApiInterface
{

    /**
     * Get list of deposit by user
     * @api
     * @param string $user_id
     * @return mixed
     */
    public function getListByUser($user_id);

}