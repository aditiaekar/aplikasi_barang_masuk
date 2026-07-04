<?php

namespace App\Repositories;

use App\Models\StockMutation;

class StockMutationRepository
{
    public function store(array $data) {
        $stockMutation = new StockMutation($data);
        $stockMutation->save();

        return $stockMutation;
    }

}
