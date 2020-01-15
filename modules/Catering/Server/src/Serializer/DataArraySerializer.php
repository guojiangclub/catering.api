<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 namespace GuoJiangClub\Catering\Server\Serializer;

use League\Fractal\Serializer\ArraySerializer;

class DataArraySerializer extends ArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        if (is_null($resourceKey))
            return ['status' => true, 'data' => $data];
        if(empty($resourceKey)){
            return $data;
        }
        return [$resourceKey => $data];
    }

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array $data
     *
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        /*if (empty($resourceKey)) {
            return $data;
        }*/
        if (is_null($resourceKey))
            return ['status' => true,'data' => $data];
        if(empty($resourceKey)){
            return $data;
        }
        return [$resourceKey => $data];
        /*return ['data' => $data];*/
    }

    /**
     * Serialize null resource.
     *
     * @return array
     */
    public function null()
    {
        return ['data' => []];
    }
}
