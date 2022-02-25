<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\LocalizeFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * @author Carmen Mihaila <cami@nodesagency.com>
 */
class Request extends FormRequest
{
    const LIMIT = 1000;

    /**
     * rules.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * getLimit.
     *
     * @param int $default
     * @param int $max
     *
     * @author Majid Mvulle <mamv@nodesagency.com>
     *
     * @return int
     */
    public function getLimit(int $default = 20, int $max = self::LIMIT): int
    {
        $limit = $this->get('limit', $default);

        $limit = (int)$limit;

        return min($limit, $max);
    }

    /**
     * getAppLimit. 
     * 
     * Separate limit function for the mobile application views (load more data) 
     *
     * @param int $default
     * @param int $max
     *
     * @author Mouhamad <mouhamad@basalt.co>
     *
     * @return int
     */
    public function getAppLimit(int $default = 12, int $max = self::LIMIT): int
    {
        $limit = $this->get('limit', $default);

        return min($limit, $max);
    }    

    /**
     * getAdminLimit.
     *
     * @param int $default
     * @param int $max
     *
     * @author Mouhamad <mouhamad@basalt.co>
     *
     * @return int
     */
    public function getAdminLimit(int $default = 25, int $max = 50): int
    {
        $limit = $this->get('limit', $default);

        return min((int)$limit, $max);
    }    

    /**
     * @param array|mixed|null $keys
     *
     * @return array
     *
     * @author Carmen Mihaila <cami@nodesagency.com>
     */
    public function getAll($keys = null)
    {
        return $this->snakeCase(array_replace_recursive(
            parent::all($keys),
            $this->query() ?? []
        ));
    }

    /**
     * @param null $keys
     *
     * @return array
     *
     * @author Carmen Mihaila <cami@nodesagency.com>
     */
    public function getLocalizedParams($keys = null): array
    {
        return LocalizeFields::transformLocalized($this->getAll($keys));
    }

    /**
     * @param array $array
     *
     * @return array
     *
     * @author Carmen Mihaila <cami@nodesagency.com>
     */
    public function snakeCase(array $array): array
    {
        return array_map(
            function ($item) {
                if (\is_array($item)) {
                    $item = $this->snakeCase($item);
                }

                return $item;
            },
            $this->doSnakeCase($array)
        );
    }

    /**
     * @param array $array
     *
     * @return array
     *
     * @author Carmen Mihaila <cami@nodesagency.com>
     */
    private function doSnakeCase(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $key = Str::snake($key);

            $result[$key] = $value;
        }

        return $result;
    }
}
