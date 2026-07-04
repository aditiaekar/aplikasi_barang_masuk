<?php

namespace App\Repositories;

use App\Models\Warehouse;

class WarehouseRepository {
    public function getAllActive() {
        return Warehouse::where('is_active',1)->orderBy('name')->get();
    }
}
