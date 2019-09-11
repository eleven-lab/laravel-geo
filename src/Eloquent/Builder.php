<?php

namespace Karomap\GeoLaravel\Eloquent;

use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;

class Builder extends IlluminateBuilder
{
    /**
     * Get query result as GeoJSON.
     *
     * @param array $columns
     * @return string
     */
    public function getGeoJson($columns = ['*'])
    {
        $geoArray = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        foreach ($this->get($columns) as $model) {
            $data = json_decode($model->toGeoJson(), true);

            if ($data['type'] === 'FeatureCollection') {
                $geoArray['features'] = array_merge($geoArray['features'], $data['features']);
            } else {
                $geoArray['features'][] = $data;
            }
        }

        return json_encode($geoArray);
    }
}
