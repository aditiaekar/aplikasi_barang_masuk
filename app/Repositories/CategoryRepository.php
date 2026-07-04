<?

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository {
    public function getAllActive() {
        return Category::where('is_active',1)->orderBy('name')->get();
    }
}
