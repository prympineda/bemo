<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Card;

class CardsController extends Controller
{
    public function index (Request $request)
    {
        $cards = Card::select('*');
        
        if ($request->query('column_id'))
        {
            $cards->where('column_id', $request->query('column_id'));
        }

        if ($request->query('date'))
        {
            $cards->whereDate('created_at', '>=', date('Y-m-d H:i:s', strtotime($request->query('date'))));
        }

        if ($request->query('status') || $request->query('status') == '0')
        {
            $cards->where('status', $request->query('status'));
        }

        return response()->json([
            'success' => TRUE,
            'data' => $cards->paginate(10)
        ]);
    }

    public function store (Request $request)
    {
        $request->validate([
            'name' => 'required',
        ],[
            'name.required' => 'Card name is required.',
        ]);

        $columns = Card::where('column_id', $request->column_id)->count();

        $card = Card::create([
            'column_id' => $request->column_id,
            'order' => $columns + 1,
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => TRUE,
            'data' => $card->toArray(),
            'message' => 'Successfully created the card.'
        ]);

    }

    public function update (Request $request, $cardId)
    {
        $request->validate([
            'name' => 'required',
        ],[
            'name.required' => 'Card name is required.',
        ]);

        $card = Card::where('id', $cardId)
                    ->firstOrFail();
         
        $card->name = $request->name;
        $card->description = $request->description;
        $card->save();

        return response()->json([
            'success' => TRUE,
            'data' => $card->toArray(),
            'message' => 'Successfully updated the card.'
        ]);
    }

    public function updateOrder (Request $request)
    {
        $selectedCard = Card::where('id', $request->card_id)->first();
        
        if ($selectedCard->column_id == $request->column_id)
        {
            $cards = Card::where('column_id', $selectedCard->column_id);

            if ($request->order > $selectedCard->order)
            {
                $cards->where('order', '<=', $request->order);
                $cards->where('order', '>', $selectedCard->order);
    
            }
            else
            {
                $cards->where('order', '<', $selectedCard->order);
                $cards->where('order', '>=', $request->order);
            }
    
            $cards = $cards->orderBy('order')->get();
    
            foreach ($cards as $card)
            {
                if ($request->order > $selectedCard->order)
                {
                    $card->order = $card->order - 1;
                }
                else
                {
                    $card->order = $card->order + 1;
                }
                
                $card->save();
            }
        }
        else
        {
            $cards = Card::where('column_id', $selectedCard->column_id)
                         ->where('order', '>', $selectedCard->order)
                         ->orderBy('order')
                         ->get();
    
            foreach ($cards as $card)
            {
                $card->order = $card->order - 1;
                $card->save();
            }

            $cards = Card::where('column_id', $request->column_id)
                         ->where('order', '>=', $request->order)
                         ->orderBy('order')
                         ->get();

            foreach ($cards as $card)
            {
                $card->order = $card->order + 1;
                $card->save();
            }

            $selectedCard->column_id = $request->column_id;
        }

        $selectedCard->order = $request->order;
        $selectedCard->save();

        return response()->json([
            'success' => TRUE,
            'message' => 'Successfully updated the card.'
        ]);
    }
}
