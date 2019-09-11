<?php

namespace Karomap\GeoLaravel\Eloquent;

use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;
use Karomap\GeoLaravel\Exceptions\GeoException;

class Builder extends IlluminateBuilder
{
    /**
     * Get query result as GeoJSON.
     *
     * @param array $columns
     * @return string
     * @throws \Karomap\GeoLaravel\Exceptions\GeoException
     */
    public function getGeoJson($columns = ['*'])
    {
        $geoArray = [
            'type' => 'FeatureCollection',
            'features' => [],
        ];

        /** @var Model $model */
        $model = $this->getModel();
        $geoms = array_flatten($model->getGeometries());

        if (!count($geoms)) {
            throw new GeoException('Error: No visible geometry attribute found.');
        }

        if ($columns != ['*']) {
            $columns = array_values(array_unique(array_merge($columns, $geoms)));
        }

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
