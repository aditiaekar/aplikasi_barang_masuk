<?

namespace App\Repositories;

use App\Models\Unit;

class UnitRepository{

    public function getAllActive() {
        return Unit::where('is_active',1)->orderBy('name')->get();
    }
}
