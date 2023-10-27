<?php

namespace app\modules\catalog\models\search;

use app\modules\admin\models\Items;
use yii\data\DataFilter;
use app\modules\admin\models\Category;

class CategorySearch extends DataFilter
{
    public ?int $parent_id = null;
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
            [['parent_id'], 'integer'],
            [['parent_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
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
        $query = [['c.published' => Items::PUBLISHED]];

        if (!empty($this->parent_id)) {
            $query[] = ['c.parent_id' => $this->parent_id];
        } else {
            $query[] = ['IS', 'c.parent_id', null];
        }

        if (!empty($this->title)) {
            $query[] = ['like', 'd.title', $this->title];
        }

        if ($query) {
            array_unshift($query, 'AND');
        }
        return $query;
    }
}