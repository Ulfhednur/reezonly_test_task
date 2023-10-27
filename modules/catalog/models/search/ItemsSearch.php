<?php

namespace app\modules\catalog\models\search;

use app\modules\admin\models\Items;
use yii\data\DataFilter;

class ItemsSearch extends DataFilter
{
    public ?string $title = null;


    /**
     * {@inheritdoc}
     */
    public function formName(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['title'], 'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function build($runValidation = true): array|bool
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }
        $query = [['i.published' => Items::PUBLISHED]];

        if (!empty($this->title)) {
            $query[] = ['like', 'd.title', $this->title];
        }

        if ($query) {
            array_unshift($query, 'AND');
        }
        return $query;
    }
}