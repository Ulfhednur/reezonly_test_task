<?php

namespace app\modules\admin\models\search;

use yii\data\DataFilter;
use app\modules\admin\models\Category;

class ItemsSearch extends DataFilter
{
    public ?int $category_id = null;
    public ?string $title = null;
    public bool $needJoin = false;


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
            [['category_id'], 'integer'],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
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
        $query = [];

        if (!empty($this->category_id)) {
            $this->needJoin = true;
            $query[] = ['ic.category_id' => $this->category_id];
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