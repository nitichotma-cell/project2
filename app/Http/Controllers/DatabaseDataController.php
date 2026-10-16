<?php

namespace App\Http\Controllers;

use App\Models\MediaItem;
use App\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DatabaseDataController extends Controller
{
    public function index()
    {
        $definitions = [
            ['label' => 'หนังสือและภาพยนตร์', 'model' => MediaItem::class],
            ['label' => 'รีวิว', 'model' => Review::class],
        ];

        $tables = [];

        foreach ($definitions as $definition) {
            /** @var Model $model */
            $model = new $definition['model'];
            $columns = Schema::getColumnListing($model->getTable());
            $columns = array_values(array_diff(
                $columns,
                $definition['hidden_columns'] ?? []
            ));

            $query = $model->newQuery();

            if ($model instanceof Review) {
                $query->orderByDesc('created_at')->orderByDesc('id');
            } else {
                $query->orderBy('id');
            }

            $tables[] = [
                'label' => $definition['label'],
                'name' => $model->getTable(),
                'columns' => $columns,
                'records' => $query->get($columns),
            ];
        }

        return view('data.index', compact('tables'));
    }
}
