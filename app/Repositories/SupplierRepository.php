<?php

namespace App\Repositories;

use App\Models\Supplier;

class SupplierRepository{
    public function getAll() {
        return Supplier::orderBy('name')->get();
    }
}
