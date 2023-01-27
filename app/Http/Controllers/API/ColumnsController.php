<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Column;
use App\Models\Card;

class ColumnsController extends Controller
{
    public function index ()
    {
        $lists = Column::select('columns.id as column_id', 'columns.name as column_name', 'cards.id as card_id', 'cards.order as order', 'cards.name as card_name', 'cards.description as card_description')
                        ->leftJoin('cards', function($leftJoin){
                            $leftJoin->on('cards.column_id', 'columns.id');
                            $leftJoin->where('cards.status', 1);
                        })
                        ->orderBy('order', 'DESC')
                        ->get();

        $columns = $lists->unique('column_id')->toArray();

        $data = [];

        foreach ($columns as $column)
        {
            $cols = [];
            $cols['name'] = $column['column_name'];
            $cols['id'] = $column['column_id'];

            $columnId = $column['column_id'];

            $listCards = $lists->filter(function($col) use ($columnId) {
                return $col->column_id == $columnId && $col->card_id;
            })->toArray();

            $cards = [];

            if (count($listCards))
            {
                $order = array_column($listCards, 'order');
                array_multisort($order, SORT_ASC, $listCards);

                foreach ($listCards as $listCard)
                {
                    $card = [];
                    $card['id'] = $listCard['card_id'];
                    $card['name'] = $listCard['card_name'];
                    $card['description'] = $listCard['card_description'];
    
                    $cards [] = $card;
                }
            }

            $cols['cards'] = $cards;

            $data [] = $cols;
        }

        return response()->json([
            'success' => TRUE,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ],[
            'name.required' => 'Column name is required.'
        ]);

        $column = Column::create([
            'name' => $request->name
        ]);

        $column->cards = [];

        return response()->json([
            'success' => TRUE,
            'data' => $column->toArray(),
            'message' => 'Successfully created the column.'
        ]);
    }

    public function delete (Request $request)
    {
        Card::where('column_id', $request->column_id)
            ->update([
                'status' => 1
            ]);

        Column::where('id', $request->column_id)->delete();

        return response()->json([
            'success' => TRUE,
            'message' => 'Successfully deleted the Column.'
        ]);
    }
}
